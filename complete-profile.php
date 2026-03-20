<?php
require_once __DIR__ . '/includes/functions.php';

$flowUserId = auth_flow_user_id();
$existingUser = $flowUserId > 0 ? fetch_user_by_id($flowUserId) : null;

if ($flowUserId > 0 && !$existingUser) {
    clear_auth_flow_session();
    set_flash('error', 'Your account session expired. Please log in again.');
    redirect('login.php');
}

if ($existingUser) {
    sync_profile_complete_flag($existingUser['id']);
    $existingUser = fetch_user_by_id($existingUser['id']);

    if ($existingUser && user_profile_is_complete($existingUser)) {
        if ((int)$existingUser['is_verified'] === 1) {
            set_authenticated_user_session($existingUser);
            redirect('profile.php');
        }

        set_verification_user_session($existingUser);
        redirect('verify.php');
    }
}

$email = $existingUser['email'] ?? trim($_SESSION['manual_email'] ?? '');
if ($email === '') {
    set_flash('error', 'Please start registration from the email step.');
    redirect('register.php');
}

function format_dob_for_display($value)
{
    $value = trim((string)$value);
    if ($value === '') {
        return '';
    }

    $date = DateTime::createFromFormat('Y-m-d', $value);
    return $date ? $date->format('d/m/Y') : $value;
}

function parse_dob_from_display($value)
{
    $value = trim((string)$value);

    if ($value === '') {
        return [null, 'Date of birth is required.'];
    }

    $date = DateTime::createFromFormat('d/m/Y', $value);
    $errors = DateTime::getLastErrors();
    $hasDateIssues = is_array($errors) && (
        (($errors['warning_count'] ?? 0) > 0) ||
        (($errors['error_count'] ?? 0) > 0)
    );

    if (
        !$date ||
        $hasDateIssues ||
        $date->format('d/m/Y') !== $value
    ) {
        return [null, 'Use the European format DD/MM/YYYY.'];
    }

    return [$date->format('Y-m-d'), null];
}

$errors = [];
$fieldErrors = [];
$isExistingFlow = (bool)$existingUser;
$passwordRequired = !$isExistingFlow || empty($existingUser['password_hash']);
$setFieldError = static function (&$fieldErrors, $field, $message) {
    if (!isset($fieldErrors[$field])) {
        $fieldErrors[$field] = $message;
    }
};

$fullName = $existingUser['full_name'] ?? '';
$username = $existingUser['username'] ?? '';
$university = $existingUser['university'] ?? '';
$phone = $existingUser['phone'] ?? '';
$dob = $existingUser['dob'] ?? '';
$dobDisplay = format_dob_for_display($dob);
$country = $existingUser['country'] ?? '';
$city = $existingUser['city'] ?? '';
$address = $existingUser['address'] ?? '';
$postcode = $existingUser['postcode'] ?? '';
$password = '';
$repeatPassword = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullName = trim($_POST['fullname'] ?? '');
    $username = trim($_POST['username'] ?? '');
    $university = trim($_POST['university'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $dobDisplay = trim($_POST['dob'] ?? '');
    $country = trim($_POST['country'] ?? '');
    $city = trim($_POST['city'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $postcode = trim($_POST['postcode'] ?? '');
    $password = $_POST['password'] ?? '';
    $repeatPassword = $_POST['repeat_password'] ?? '';

    if (!verify_csrf()) {
        $errors[] = 'Invalid request token. Please try again.';
    }

    $nameParts = split_full_name($fullName);
    if (count($nameParts['parts']) < 2 || count($nameParts['parts']) > 3) {
        $setFieldError($fieldErrors, 'fullname', 'Full name must contain 2 or 3 words.');
    }

    if ($username === '' || !preg_match('/^[a-zA-Z0-9._]{3,30}$/', $username)) {
        $setFieldError($fieldErrors, 'username', 'Username must be 3-30 characters and use only letters, numbers, dots, or underscores.');
    }

    if ($university === '') {
        $setFieldError($fieldErrors, 'university', 'University is required.');
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $setFieldError($fieldErrors, 'email_preview', 'Email address is not valid. Please restart registration.');
    }

    if ($phone === '' || !preg_match('/^\+?[0-9]{7,15}$/', $phone)) {
        $setFieldError($fieldErrors, 'phone', 'Phone number must use 7-15 digits and may start with +.');
    }

    [$parsedDob, $dobError] = parse_dob_from_display($dobDisplay);
    if ($dobError !== null) {
        $setFieldError($fieldErrors, 'dob', $dobError);
    } else {
        $dob = $parsedDob;
    }

    if ($country === '') {
        $setFieldError($fieldErrors, 'country', 'Country is required.');
    }

    if ($city === '') {
        $setFieldError($fieldErrors, 'city', 'City is required.');
    }

    if ($address === '') {
        $setFieldError($fieldErrors, 'address', 'Address is required.');
    }

    if ($postcode === '') {
        $setFieldError($fieldErrors, 'postcode', 'Postal code is required.');
    }

    $shouldUpdatePassword = $passwordRequired || $password !== '' || $repeatPassword !== '';
    if ($shouldUpdatePassword) {
        if (
            strlen($password) < 8 ||
            !preg_match('/[A-Z]/', $password) ||
            !preg_match('/[0-9]/', $password) ||
            !preg_match('/[\W_]/', $password)
        ) {
            $setFieldError($fieldErrors, 'password', 'Password must be at least 8 characters and include an uppercase letter, a number, and a symbol.');
        }

        if ($password !== $repeatPassword) {
            $setFieldError($fieldErrors, 'repeat_password', 'Passwords do not match.');
        }
    }

    $duplicateUsernameStmt = $isExistingFlow
        ? $mysqli->prepare("SELECT id FROM users WHERE LOWER(username) = LOWER(?) AND id != ? LIMIT 1")
        : $mysqli->prepare("SELECT id FROM users WHERE LOWER(username) = LOWER(?) LIMIT 1");

    if ($duplicateUsernameStmt) {
        if ($isExistingFlow) {
            $duplicateUsernameStmt->bind_param('si', $username, $existingUser['id']);
        } else {
            $duplicateUsernameStmt->bind_param('s', $username);
        }
        $duplicateUsernameStmt->execute();
        $duplicateUsername = $duplicateUsernameStmt->get_result()->fetch_assoc();
        $duplicateUsernameStmt->close();

        if ($duplicateUsername) {
            $setFieldError($fieldErrors, 'username', 'This username already exists. Please choose a different one.');
        }
    } else {
        $errors[] = 'We could not validate your username right now.';
    }

    if (!$isExistingFlow) {
        $duplicateEmailStmt = $mysqli->prepare("SELECT id FROM users WHERE LOWER(email) = LOWER(?) LIMIT 1");
        $duplicateEmailStmt->bind_param('s', $email);
        $duplicateEmailStmt->execute();
        $duplicateEmail = $duplicateEmailStmt->get_result()->fetch_assoc();
        $duplicateEmailStmt->close();

        if ($duplicateEmail) {
            $setFieldError($fieldErrors, 'email_preview', 'An account with this email already exists.');
        }
    }

    if (!$errors && !$fieldErrors) {
        $passwordHash = $shouldUpdatePassword ? password_hash($password, PASSWORD_DEFAULT) : null;

        if ($isExistingFlow) {
            if ($shouldUpdatePassword) {
                $stmt = $mysqli->prepare("
                    UPDATE users
                    SET
                        full_name = ?,
                        first_name = ?,
                        middle_name = ?,
                        last_name = ?,
                        username = ?,
                        password_hash = ?,
                        university = ?,
                        phone = ?,
                        country = ?,
                        city = ?,
                        address = ?,
                        postcode = ?,
                        dob = ?,
                        profile_complete = 1
                    WHERE id = ?
                ");
                $stmt->bind_param(
                    'sssssssssssssi',
                    $fullName,
                    $nameParts['first_name'],
                    $nameParts['middle_name'],
                    $nameParts['last_name'],
                    $username,
                    $passwordHash,
                    $university,
                    $phone,
                    $country,
                    $city,
                    $address,
                    $postcode,
                    $dob,
                    $existingUser['id']
                );
            } else {
                $stmt = $mysqli->prepare("
                    UPDATE users
                    SET
                        full_name = ?,
                        first_name = ?,
                        middle_name = ?,
                        last_name = ?,
                        username = ?,
                        university = ?,
                        phone = ?,
                        country = ?,
                        city = ?,
                        address = ?,
                        postcode = ?,
                        dob = ?,
                        profile_complete = 1
                    WHERE id = ?
                ");
                $stmt->bind_param(
                    'ssssssssssssi',
                    $fullName,
                    $nameParts['first_name'],
                    $nameParts['middle_name'],
                    $nameParts['last_name'],
                    $username,
                    $university,
                    $phone,
                    $country,
                    $city,
                    $address,
                    $postcode,
                    $dob,
                    $existingUser['id']
                );
            }
        } else {
            $stmt = $mysqli->prepare("
                INSERT INTO users (
                    full_name,
                    first_name,
                    middle_name,
                    last_name,
                    username,
                    email,
                    password_hash,
                    is_verified,
                    university,
                    phone,
                    country,
                    city,
                    address,
                    postcode,
                    dob,
                    profile_complete
                ) VALUES (?, ?, ?, ?, ?, ?, ?, 0, ?, ?, ?, ?, ?, ?, ?, 1)
            ");
            $stmt->bind_param(
                'ssssssssssssss',
                $fullName,
                $nameParts['first_name'],
                $nameParts['middle_name'],
                $nameParts['last_name'],
                $username,
                $email,
                $passwordHash,
                $university,
                $phone,
                $country,
                $city,
                $address,
                $postcode,
                $dob
            );
        }

        if ($stmt->execute()) {
            $userId = $isExistingFlow ? (int)$existingUser['id'] : (int)$stmt->insert_id;
            $stmt->close();

            $savedUser = fetch_user_by_id($userId);
            if (!$savedUser) {
                $errors[] = 'Your profile was saved, but we could not continue the verification flow.';
            } elseif ((int)$savedUser['is_verified'] === 1) {
                set_authenticated_user_session($savedUser);
                redirect('profile.php');
            } else {
                unset($_SESSION['manual_email']);
                set_verification_user_session($savedUser);
                [$mailSent, $mailError] = issue_verification_code((int)$savedUser['id'], $savedUser['email'], $savedUser['full_name']);

                if ($mailSent) {
                    set_flash('success', 'We sent a 6-digit verification code to ' . $savedUser['email'] . '.');
                } else {
                    set_flash('error', 'Your profile is ready, but we could not send the verification email right away. You can resend the code on the next screen.');
                    error_log('Complete profile verification email error: ' . (string)$mailError);
                }

                redirect('verify.php');
            }
        } else {
            $stmt->close();
            $errors[] = 'We could not save your profile. Please try again.';
        }
    }
}

$stepFields = [
    1 => ['fullname', 'username', 'email_preview', 'university', 'phone', 'dob'],
    2 => ['password', 'repeat_password'],
    3 => ['country', 'city', 'address', 'postcode'],
];

$initialStep = 1;
foreach ($stepFields as $stepNumber => $fields) {
    foreach ($fields as $fieldName) {
        if (isset($fieldErrors[$fieldName])) {
            $initialStep = $stepNumber;
            break 2;
        }
    }
}

require_once __DIR__ . '/includes/header.php';
?>

<section class="auth-card auth-card--wizard wide-card">
    <div class="auth-heading">
        <span class="eyebrow"><?= $isExistingFlow ? 'Finish setup' : 'Complete profile'; ?></span>
        <h1>Build Your Student Marketplace Profile</h1>
        <p>Follow the same upgraded onboarding flow: identity, account security, then contact and address details.</p>
    </div>

    <?php if ($errors || $fieldErrors): ?>
        <div class="alert alert-error">
            <?php if ($fieldErrors): ?>
                <div>Please review the highlighted fields and complete every required detail before continuing.</div>
            <?php endif; ?>
            <?php foreach ($errors as $error): ?>
                <div><?= e($error); ?></div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <form method="POST" class="form-card wizard-shell" id="complete-profile-form" novalidate>
        <input type="hidden" name="csrf_token" value="<?= e(csrf_token()); ?>">

        <div class="wizard-header-row">
            <div class="wizard-copy">
                <div class="wizard-progress-text" id="wizard-progress-text">Step 1 of 3</div>
                <h2 class="wizard-step-title" id="wizard-step-title">Tell us who you are</h2>
                <p class="wizard-step-description" id="wizard-step-description">Start with your identity, username, university, and basic student contact details.</p>
            </div>

            <div class="wizard-stepper" aria-hidden="true">
                <div class="wizard-step-indicator is-active" data-indicator-step="1">
                    <span>1</span>
                    <strong>Identity</strong>
                </div>
                <div class="wizard-step-indicator" data-indicator-step="2">
                    <span>2</span>
                    <strong>Security</strong>
                </div>
                <div class="wizard-step-indicator" data-indicator-step="3">
                    <span>3</span>
                    <strong>Address</strong>
                </div>
            </div>
        </div>

        <div class="wizard-progress" aria-hidden="true">
            <div class="wizard-progress-bar" id="wizard-progress-bar"></div>
        </div>

        <div class="wizard-step<?= $initialStep === 1 ? ' is-active' : ''; ?>" data-step="1"<?= $initialStep === 1 ? '' : ' hidden'; ?>>
            <div class="form-grid two-cols">
                <div class="form-group">
                    <label for="fullname">Full Name</label>
                    <input id="fullname" type="text" name="fullname" value="<?= e($fullName); ?>" placeholder="e.g. Maria Ioannou" autocomplete="name" required class="<?= isset($fieldErrors['fullname']) ? 'is-invalid' : ''; ?>">
                    <small class="input-help">Use 2 or 3 words: first last or first middle last.</small>
                    <small class="input-help is-error field-error" data-error-for="fullname"<?= isset($fieldErrors['fullname']) ? '' : ' hidden'; ?>><?= isset($fieldErrors['fullname']) ? e($fieldErrors['fullname']) : ''; ?></small>
                </div>

                <div class="form-group">
                    <label for="username">Username</label>
                    <input id="username" type="text" name="username" value="<?= e($username); ?>" minlength="3" maxlength="30" pattern="[a-zA-Z0-9._]{3,30}" autocomplete="username" required class="<?= isset($fieldErrors['username']) ? 'is-invalid' : ''; ?>">
                    <small id="username-status" class="input-help" hidden></small>
                    <small class="input-help is-error field-error" data-error-for="username"<?= isset($fieldErrors['username']) ? '' : ' hidden'; ?>><?= isset($fieldErrors['username']) ? e($fieldErrors['username']) : ''; ?></small>
                </div>
            </div>

            <div class="form-grid two-cols">
                <div class="form-group">
                    <label for="email_preview">Email Address</label>
                    <input id="email_preview" type="email" name="email_preview" value="<?= e($email); ?>" readonly class="readonly-input <?= isset($fieldErrors['email_preview']) ? 'is-invalid' : ''; ?>">
                    <small class="input-help">Your verification code will be sent to this email after step 3.</small>
                    <small class="input-help is-error field-error" data-error-for="email_preview"<?= isset($fieldErrors['email_preview']) ? '' : ' hidden'; ?>><?= isset($fieldErrors['email_preview']) ? e($fieldErrors['email_preview']) : ''; ?></small>
                </div>

                <div class="form-group">
                    <label for="university">University</label>
                    <input id="university" type="text" name="university" value="<?= e($university); ?>" placeholder="e.g. University of Cyprus" autocomplete="organization" required class="<?= isset($fieldErrors['university']) ? 'is-invalid' : ''; ?>">
                    <small class="input-help is-error field-error" data-error-for="university"<?= isset($fieldErrors['university']) ? '' : ' hidden'; ?>><?= isset($fieldErrors['university']) ? e($fieldErrors['university']) : ''; ?></small>
                </div>
            </div>

            <div class="form-grid two-cols">
                <div class="form-group">
                    <label for="phone">Phone Number</label>
                    <input id="phone" type="tel" name="phone" value="<?= e($phone); ?>" placeholder="+35799123456" autocomplete="tel" required class="<?= isset($fieldErrors['phone']) ? 'is-invalid' : ''; ?>">
                    <small class="input-help">Use 7 to 15 digits. You can start with + for Cyprus numbers.</small>
                    <small class="input-help is-error field-error" data-error-for="phone"<?= isset($fieldErrors['phone']) ? '' : ' hidden'; ?>><?= isset($fieldErrors['phone']) ? e($fieldErrors['phone']) : ''; ?></small>
                </div>

                <div class="form-group">
                    <label for="dob">Date of Birth</label>
                    <input id="dob" type="text" name="dob" value="<?= e($dobDisplay); ?>" placeholder="dd/mm/yyyy" inputmode="numeric" maxlength="10" autocomplete="bday" required class="<?= isset($fieldErrors['dob']) ? 'is-invalid' : ''; ?>">
                    <small class="input-help">Use the European format DD/MM/YYYY.</small>
                    <small class="input-help is-error field-error" data-error-for="dob"<?= isset($fieldErrors['dob']) ? '' : ' hidden'; ?>><?= isset($fieldErrors['dob']) ? e($fieldErrors['dob']) : ''; ?></small>
                </div>
            </div>
        </div>

        <div class="wizard-step<?= $initialStep === 2 ? ' is-active' : ''; ?>" data-step="2"<?= $initialStep === 2 ? '' : ' hidden'; ?>>
            <div class="security-stack">
                <div class="form-group security-field">
                    <label for="password">Password</label>
                    <div class="password-row">
                        <input id="password" type="password" name="password" autocomplete="new-password" <?= $passwordRequired ? 'required' : ''; ?> class="<?= isset($fieldErrors['password']) ? 'is-invalid' : ''; ?>">
                        <button type="button" class="password-toggle js-toggle-password" data-target="#password" aria-label="Show password">Show</button>
                    </div>
                    <small class="input-help">
                        <?= $passwordRequired ? 'Required.' : 'Optional for existing accounts. Leave blank to keep your current password.'; ?>
                        Must include 8+ characters, an uppercase letter, a number, and a symbol.
                    </small>
                    <small class="input-help is-error field-error" data-error-for="password"<?= isset($fieldErrors['password']) ? '' : ' hidden'; ?>><?= isset($fieldErrors['password']) ? e($fieldErrors['password']) : ''; ?></small>
                    <div class="password-checklist" id="password-checklist">
                        <span id="check-length">At least 8 characters</span>
                        <span id="check-uppercase">An uppercase letter</span>
                        <span id="check-number">A number</span>
                        <span id="check-symbol">A symbol</span>
                    </div>
                </div>

                <div class="form-group security-field">
                    <label for="repeat_password">Confirm Password</label>
                    <div class="password-row">
                        <input id="repeat_password" type="password" name="repeat_password" autocomplete="new-password" <?= $passwordRequired ? 'required' : ''; ?> class="<?= isset($fieldErrors['repeat_password']) ? 'is-invalid' : ''; ?>">
                        <button type="button" class="password-toggle js-toggle-password" data-target="#repeat_password" aria-label="Show password">Show</button>
                    </div>
                    <small class="input-help">Re-enter the same password so we can confirm it matches.</small>
                    <small class="input-help is-error field-error" data-error-for="repeat_password"<?= isset($fieldErrors['repeat_password']) ? '' : ' hidden'; ?>><?= isset($fieldErrors['repeat_password']) ? e($fieldErrors['repeat_password']) : ''; ?></small>
                </div>
            </div>
        </div>

        <div class="wizard-step<?= $initialStep === 3 ? ' is-active' : ''; ?>" data-step="3"<?= $initialStep === 3 ? '' : ' hidden'; ?>>
            <div class="form-grid two-cols">
                <div class="form-group">
                    <label for="country">Country</label>
                    <input id="country" type="text" name="country" value="<?= e($country); ?>" placeholder="Cyprus" autocomplete="country-name" required class="<?= isset($fieldErrors['country']) ? 'is-invalid' : ''; ?>">
                    <small class="input-help is-error field-error" data-error-for="country"<?= isset($fieldErrors['country']) ? '' : ' hidden'; ?>><?= isset($fieldErrors['country']) ? e($fieldErrors['country']) : ''; ?></small>
                </div>

                <div class="form-group">
                    <label for="city">City</label>
                    <input id="city" type="text" name="city" value="<?= e($city); ?>" autocomplete="address-level2" required class="<?= isset($fieldErrors['city']) ? 'is-invalid' : ''; ?>">
                    <small class="input-help is-error field-error" data-error-for="city"<?= isset($fieldErrors['city']) ? '' : ' hidden'; ?>><?= isset($fieldErrors['city']) ? e($fieldErrors['city']) : ''; ?></small>
                </div>
            </div>

            <div class="form-grid two-cols">
                <div class="form-group">
                    <label for="address">Address</label>
                    <input id="address" type="text" name="address" value="<?= e($address); ?>" autocomplete="street-address" required class="<?= isset($fieldErrors['address']) ? 'is-invalid' : ''; ?>">
                    <small class="input-help is-error field-error" data-error-for="address"<?= isset($fieldErrors['address']) ? '' : ' hidden'; ?>><?= isset($fieldErrors['address']) ? e($fieldErrors['address']) : ''; ?></small>
                </div>

                <div class="form-group">
                    <label for="postcode">Postal Code</label>
                    <input id="postcode" type="text" name="postcode" value="<?= e($postcode); ?>" autocomplete="postal-code" required class="<?= isset($fieldErrors['postcode']) ? 'is-invalid' : ''; ?>">
                    <small class="input-help is-error field-error" data-error-for="postcode"<?= isset($fieldErrors['postcode']) ? '' : ' hidden'; ?>><?= isset($fieldErrors['postcode']) ? e($fieldErrors['postcode']) : ''; ?></small>
                </div>
            </div>

            <div class="wizard-stage-note">
                After you finish setup, UniTrade CY will immediately email a 6-digit verification code to <strong><?= e($email); ?></strong>.
            </div>
        </div>

        <div class="wizard-actions-row">
            <button type="button" class="btn btn-secondary" id="prev-step" hidden>Back</button>
            <button type="button" class="btn btn-primary" id="next-step">Next</button>
        </div>
    </form>

    <p class="muted-text">Already registered? <a class="text-link" href="login.php">Return to login</a>.</p>
</section>

<script>
document.addEventListener('DOMContentLoaded', function () {
    var form = document.getElementById('complete-profile-form');
    var steps = Array.prototype.slice.call(document.querySelectorAll('.wizard-step'));
    var stepMeta = {
        1: {
            title: 'Tell us who you are',
            description: 'Start with your identity, username, university, and basic student contact details.'
        },
        2: {
            title: 'Protect your account',
            description: 'Choose a strong password so your UniTrade CY account stays secure.'
        },
        3: {
            title: 'Add your address details',
            description: 'Finish your location details so the marketplace profile is complete and ready for verification.'
        }
    };
    var stepFieldMap = {
        1: ['fullname', 'username', 'email_preview', 'university', 'phone', 'dob'],
        2: ['password', 'repeat_password'],
        3: ['country', 'city', 'address', 'postcode']
    };
    var fieldLabels = {
        fullname: 'Full name',
        username: 'Username',
        email_preview: 'Email address',
        university: 'University',
        phone: 'Phone number',
        dob: 'Date of birth',
        password: 'Password',
        repeat_password: 'Confirm password',
        country: 'Country',
        city: 'City',
        address: 'Address',
        postcode: 'Postal code'
    };
    var currentStep = <?= (int)$initialStep; ?>;
    var totalSteps = steps.length;
    var progressBar = document.getElementById('wizard-progress-bar');
    var progressText = document.getElementById('wizard-progress-text');
    var stepTitle = document.getElementById('wizard-step-title');
    var stepDescription = document.getElementById('wizard-step-description');
    var prevButton = document.getElementById('prev-step');
    var nextButton = document.getElementById('next-step');
    var stepIndicators = Array.prototype.slice.call(document.querySelectorAll('.wizard-step-indicator'));
    var usernameInput = document.getElementById('username');
    var usernameStatus = document.getElementById('username-status');
    var csrfTokenInput = document.querySelector('input[name="csrf_token"]');
    var passwordInput = document.getElementById('password');
    var confirmInput = document.getElementById('repeat_password');
    var dobInput = document.getElementById('dob');
    var passwordRequired = <?= $passwordRequired ? 'true' : 'false'; ?>;
    var initialUsername = <?= json_encode((string)$username); ?>;
    var usernameValid = false;
    var usernameChecked = false;
    var usernameCheckPending = false;
    var usernameCheckedValue = '';
    var debounceTimer = null;
    var isSubmitting = false;

    function getField(fieldName) {
        return document.querySelector('[name="' + fieldName + '"]') || document.getElementById(fieldName);
    }

    function getErrorElement(fieldName) {
        return document.querySelector('[data-error-for="' + fieldName + '"]');
    }

    function showFieldError(fieldName, message) {
        var field = getField(fieldName);
        var errorElement = getErrorElement(fieldName);

        if (field) {
            field.classList.add('is-invalid');
            field.setAttribute('aria-invalid', 'true');
        }

        if (errorElement) {
            errorElement.textContent = message;
            errorElement.hidden = false;
        }
    }

    function clearFieldError(fieldName) {
        var field = getField(fieldName);
        var errorElement = getErrorElement(fieldName);

        if (field) {
            field.classList.remove('is-invalid');
            field.removeAttribute('aria-invalid');
        }

        if (errorElement) {
            errorElement.textContent = '';
            errorElement.hidden = true;
        }
    }

    function updatePasswordChecklist() {
        var value = passwordInput.value;
        var checks = {
            length: value.length >= 8,
            uppercase: /[A-Z]/.test(value),
            number: /[0-9]/.test(value),
            symbol: /[\W_]/.test(value)
        };

        document.getElementById('check-length').classList.toggle('is-valid', checks.length);
        document.getElementById('check-uppercase').classList.toggle('is-valid', checks.uppercase);
        document.getElementById('check-number').classList.toggle('is-valid', checks.number);
        document.getElementById('check-symbol').classList.toggle('is-valid', checks.symbol);
    }

    function formatDobValue(value) {
        var digits = value.replace(/\D/g, '').slice(0, 8);

        if (digits.length <= 2) {
            return digits;
        }

        if (digits.length <= 4) {
            return digits.slice(0, 2) + '/' + digits.slice(2);
        }

        return digits.slice(0, 2) + '/' + digits.slice(2, 4) + '/' + digits.slice(4);
    }

    function getDobError(value) {
        var trimmedValue = value.trim();
        var parts;
        var day;
        var month;
        var year;
        var candidateDate;
        var today = new Date();

        if (!trimmedValue) {
            return 'Date of birth is required.';
        }

        if (!/^\d{2}\/\d{2}\/\d{4}$/.test(trimmedValue)) {
            return 'Use the European format DD/MM/YYYY.';
        }

        parts = trimmedValue.split('/');
        day = Number(parts[0]);
        month = Number(parts[1]);
        year = Number(parts[2]);
        candidateDate = new Date(year, month - 1, day);

        if (
            candidateDate.getFullYear() !== year ||
            candidateDate.getMonth() !== month - 1 ||
            candidateDate.getDate() !== day
        ) {
            return 'Enter a valid calendar date.';
        }

        today.setHours(0, 0, 0, 0);
        if (candidateDate > today) {
            return 'Date of birth cannot be in the future.';
        }

        return '';
    }

    function showStep(stepNumber) {
        stepNumber = Math.max(1, Math.min(stepNumber, totalSteps));
        currentStep = stepNumber;

        steps.forEach(function (step) {
            var isActive = Number(step.getAttribute('data-step')) === stepNumber;
            step.classList.toggle('is-active', isActive);
            step.hidden = !isActive;
        });

        stepIndicators.forEach(function (indicator) {
            var indicatorStep = Number(indicator.getAttribute('data-indicator-step'));
            indicator.classList.toggle('is-active', indicatorStep === stepNumber);
            indicator.classList.toggle('is-complete', indicatorStep < stepNumber);
        });

        progressBar.style.width = ((stepNumber / totalSteps) * 100) + '%';
        progressText.textContent = 'Step ' + stepNumber + ' of ' + totalSteps;
        stepTitle.textContent = stepMeta[stepNumber].title;
        stepDescription.textContent = stepMeta[stepNumber].description;
        prevButton.hidden = stepNumber === 1;
        nextButton.textContent = stepNumber === totalSteps ? 'Finish Setup' : 'Next';
    }

    function updateUsernameStatus(message, stateClass) {
        usernameStatus.textContent = message;
        usernameStatus.classList.remove('is-error', 'is-success');
        usernameStatus.hidden = !message;
        if (stateClass && message) {
            usernameStatus.classList.add(stateClass);
        }
    }

    function scheduleUsernameCheck(delayMs) {
        var value = usernameInput.value.trim();

        usernameChecked = false;
        usernameValid = false;
        usernameCheckPending = false;
        usernameCheckedValue = '';
        window.clearTimeout(debounceTimer);

        if (!value) {
            updateUsernameStatus('', '');
            clearFieldError('username');
            return;
        }

        if (!/^[a-zA-Z0-9._]{3,30}$/.test(value)) {
            updateUsernameStatus('', '');
            return;
        }

        if (initialUsername && value.toLowerCase() === initialUsername.trim().toLowerCase()) {
            usernameChecked = true;
            usernameValid = true;
            usernameCheckedValue = value;
            updateUsernameStatus('Username is ready to use.', 'is-success');
            clearFieldError('username');
            return;
        }

        debounceTimer = window.setTimeout(function () {
            usernameCheckPending = true;
            updateUsernameStatus('Checking availability...', '');

            fetch('check-username.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'username=' + encodeURIComponent(value) + '&csrf_token=' + encodeURIComponent(csrfTokenInput.value)
            })
                .then(function (response) { return response.text(); })
                .then(function (status) {
                    if (usernameInput.value.trim() !== value) {
                        return;
                    }

                    usernameChecked = true;
                    usernameCheckPending = false;
                    usernameCheckedValue = value;

                    if (status.trim() === 'taken') {
                        usernameValid = false;
                        updateUsernameStatus('', '');
                        showFieldError('username', 'This username already exists. Please choose a different one.');
                    } else if (status.trim() === 'available') {
                        usernameValid = true;
                        clearFieldError('username');
                        updateUsernameStatus('Username is available.', 'is-success');
                    } else {
                        usernameValid = false;
                        updateUsernameStatus('', '');
                        showFieldError('username', 'Could not check username right now. Please try again.');
                    }
                })
                .catch(function () {
                    if (usernameInput.value.trim() !== value) {
                        return;
                    }

                    usernameChecked = true;
                    usernameCheckPending = false;
                    usernameCheckedValue = value;
                    usernameValid = false;
                    updateUsernameStatus('', '');
                    showFieldError('username', 'Could not check username right now. Please try again.');
                });
        }, delayMs);
    }

    function ensureUsernameChecked() {
        var value = usernameInput.value.trim();

        if (!value || !/^[a-zA-Z0-9._]{3,30}$/.test(value)) {
            return Promise.resolve(false);
        }

        if (initialUsername && value.toLowerCase() === initialUsername.trim().toLowerCase()) {
            usernameChecked = true;
            usernameValid = true;
            usernameCheckedValue = value;
            return Promise.resolve(true);
        }

        if (usernameCheckPending) {
            showFieldError('username', 'Please wait until the username availability check finishes.');
            return Promise.resolve(false);
        }

        if (usernameChecked && usernameCheckedValue === value) {
            return Promise.resolve(usernameValid);
        }

        return new Promise(function (resolve) {
            usernameCheckPending = true;
            updateUsernameStatus('Checking availability...', '');

            fetch('check-username.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'username=' + encodeURIComponent(value) + '&csrf_token=' + encodeURIComponent(csrfTokenInput.value)
            })
                .then(function (response) { return response.text(); })
                .then(function (status) {
                    usernameChecked = true;
                    usernameCheckPending = false;
                    usernameCheckedValue = value;

                    if (status.trim() === 'available') {
                        usernameValid = true;
                        clearFieldError('username');
                        updateUsernameStatus('Username is available.', 'is-success');
                        resolve(true);
                        return;
                    }

                    usernameValid = false;
                    updateUsernameStatus('', '');
                    showFieldError('username', status.trim() === 'taken'
                        ? 'This username already exists. Please choose a different one.'
                        : 'Could not check username right now. Please try again.');
                    resolve(false);
                })
                .catch(function () {
                    usernameChecked = true;
                    usernameCheckPending = false;
                    usernameCheckedValue = value;
                    usernameValid = false;
                    updateUsernameStatus('', '');
                    showFieldError('username', 'Could not check username right now. Please try again.');
                    resolve(false);
                });
        });
    }

    function validateField(fieldName) {
        var field = getField(fieldName);
        var value = field ? field.value.trim() : '';
        var nameParts;
        var shouldValidatePassword;

        if (!field) {
            return true;
        }

        if (fieldName !== 'username') {
            clearFieldError(fieldName);
        }

        switch (fieldName) {
            case 'fullname':
                if (!value) {
                    showFieldError(fieldName, 'Full name is required.');
                    return false;
                }
                nameParts = value.split(/\s+/);
                if (nameParts.length < 2 || nameParts.length > 3) {
                    showFieldError(fieldName, 'Full name must contain 2 or 3 words.');
                    return false;
                }
                return true;

            case 'username':
                if (!value) {
                    updateUsernameStatus('', '');
                    showFieldError(fieldName, 'Username is required.');
                    return false;
                }
                if (!/^[a-zA-Z0-9._]{3,30}$/.test(value)) {
                    updateUsernameStatus('', '');
                    showFieldError(fieldName, 'Username must be 3-30 characters and use only letters, numbers, dots, or underscores.');
                    return false;
                }
                if (usernameCheckPending) {
                    showFieldError(fieldName, 'Please wait until the username availability check finishes.');
                    return false;
                }
                if (!usernameChecked || usernameCheckedValue !== value) {
                    showFieldError(fieldName, 'Please wait while we check if this username is available.');
                    return false;
                }
                if (!usernameValid) {
                    showFieldError(fieldName, 'This username already exists. Please choose a different one.');
                    return false;
                }
                clearFieldError(fieldName);
                return true;

            case 'email_preview':
                if (!value) {
                    showFieldError(fieldName, 'Email address is missing. Please restart registration.');
                    return false;
                }
                return true;

            case 'university':
            case 'country':
            case 'city':
            case 'address':
            case 'postcode':
                if (!value) {
                    showFieldError(fieldName, fieldLabels[fieldName] + ' is required.');
                    return false;
                }
                return true;

            case 'phone':
                if (!value) {
                    showFieldError(fieldName, 'Phone number is required.');
                    return false;
                }
                if (!/^\+?[0-9]{7,15}$/.test(value)) {
                    showFieldError(fieldName, 'Phone number must use 7-15 digits and may start with +.');
                    return false;
                }
                return true;

            case 'dob':
                var dobError = getDobError(value);
                if (dobError) {
                    showFieldError(fieldName, dobError);
                    return false;
                }
                return true;

            case 'password':
                shouldValidatePassword = passwordRequired || passwordInput.value !== '' || confirmInput.value !== '';
                if (!shouldValidatePassword) {
                    return true;
                }
                if (!passwordInput.value) {
                    showFieldError(fieldName, 'Password is required.');
                    return false;
                }
                if (
                    passwordInput.value.length < 8 ||
                    !/[A-Z]/.test(passwordInput.value) ||
                    !/[0-9]/.test(passwordInput.value) ||
                    !/[\W_]/.test(passwordInput.value)
                ) {
                    showFieldError(fieldName, 'Password must be at least 8 characters and include an uppercase letter, a number, and a symbol.');
                    return false;
                }
                return true;

            case 'repeat_password':
                shouldValidatePassword = passwordRequired || passwordInput.value !== '' || confirmInput.value !== '';
                if (!shouldValidatePassword) {
                    return true;
                }
                if (!confirmInput.value) {
                    showFieldError(fieldName, 'Please confirm your password.');
                    return false;
                }
                if (passwordInput.value !== confirmInput.value) {
                    showFieldError(fieldName, 'Passwords do not match.');
                    return false;
                }
                return true;

            default:
                return true;
        }
    }

    function validateStep(stepNumber) {
        var fields = stepFieldMap[stepNumber] || [];

        return fields.every(function (fieldName) {
            return validateField(fieldName);
        });
    }

    function getFirstInvalidStep() {
        var stepNumber;

        for (stepNumber = 1; stepNumber <= totalSteps; stepNumber += 1) {
            if (!validateStep(stepNumber)) {
                return stepNumber;
            }
        }

        return null;
    }

    async function submitWizard() {
        var firstInvalidStep;

        if (isSubmitting) {
            return;
        }

        await ensureUsernameChecked();
        firstInvalidStep = getFirstInvalidStep();

        if (firstInvalidStep !== null) {
            showStep(firstInvalidStep);
            return;
        }

        isSubmitting = true;
        nextButton.disabled = true;
        prevButton.disabled = true;
        nextButton.textContent = 'Sending Verification...';
        HTMLFormElement.prototype.submit.call(form);
    }

    usernameInput.addEventListener('input', function () {
        clearFieldError('username');
        scheduleUsernameCheck(280);
    });

    usernameInput.addEventListener('blur', function () {
        scheduleUsernameCheck(0);
    });

    dobInput.addEventListener('input', function () {
        var formattedValue = formatDobValue(dobInput.value);
        if (dobInput.value !== formattedValue) {
            dobInput.value = formattedValue;
        }
        if (dobInput.value.length === 10) {
            validateField('dob');
        } else {
            clearFieldError('dob');
        }
    });

    dobInput.addEventListener('blur', function () {
        validateField('dob');
    });

    ['fullname', 'university', 'phone', 'country', 'city', 'address', 'postcode'].forEach(function (fieldName) {
        var field = getField(fieldName);

        if (!field) {
            return;
        }

        field.addEventListener('input', function () {
            if (field.value.trim() !== '') {
                validateField(fieldName);
            } else {
                clearFieldError(fieldName);
            }
        });

        field.addEventListener('blur', function () {
            validateField(fieldName);
        });
    });

    passwordInput.addEventListener('input', function () {
        updatePasswordChecklist();
        if (passwordInput.value !== '' || passwordRequired) {
            validateField('password');
        } else {
            clearFieldError('password');
        }

        if (confirmInput.value !== '') {
            validateField('repeat_password');
        }
    });

    confirmInput.addEventListener('input', function () {
        if (confirmInput.value !== '') {
            validateField('repeat_password');
        } else {
            clearFieldError('repeat_password');
        }
    });

    nextButton.addEventListener('click', async function () {
        if (isSubmitting) {
            return;
        }

        if (currentStep === 1) {
            await ensureUsernameChecked();
        }

        if (!validateStep(currentStep)) {
            return;
        }

        if (currentStep >= totalSteps) {
            submitWizard();
            return;
        }

        showStep(currentStep + 1);
    });

    prevButton.addEventListener('click', function () {
        if (currentStep > 1) {
            showStep(currentStep - 1);
        }
    });

    form.addEventListener('submit', function (event) {
        if (isSubmitting) {
            return;
        }

        event.preventDefault();
        submitWizard();
    });

    updatePasswordChecklist();
    if (usernameInput.value.trim() !== '') {
        scheduleUsernameCheck(0);
    }
    showStep(currentStep);
});
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

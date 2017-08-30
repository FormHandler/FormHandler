<?php

namespace FormHandler\Validator;

/**
 * This Validator checks if the form has a valid CSRF token.
 */
class CsrfValidator extends AbstractValidator
{
    protected $validated = null;

    /**
     * Create a new CSRF validator
     *
     * @param string $message
     */
    public function __construct($message = null)
    {
        if ($message === null) {
            $message = 'This form has expired, please try again.';
        }

        $this->setRequired(true);

        $this->setErrorMessage($message);

        // some housekeeping of the tokens...
        $this->cleanupCsrfTokens();
    }

    /**
     * Function to handle the cleanup of old/stale CSRF tokens.
     *
     * We use the global expiration time defined in the constant CSRFTOKEN_EXPIRE here,
     * unless this constant is not defined. In this case, we fall back on the default
     * value of 3600 seconds.
     */
    protected function cleanupCsrfTokens()
    {
        // Determine the expiration of the CSRF tokens.
        if (defined('CSRFTOKEN_EXPIRE')) {
            $expiry = intval(CSRFTOKEN_EXPIRE);
        } else {
            // We fall back on 3600 seconds if there is no default expiration time known.
            $expiry = 3600;
        }

        // Check if we have any tokens to clean.
        if (!isset($_SESSION) || !array_key_exists('csrftokens', $_SESSION) ||
            (is_array($_SESSION['csrftokens']) &&
                count($_SESSION['csrftokens']) === 0)
        ) {
            return; // No tokens to clean.
        }

        // Make sure $_SESSION['csrftokens'] is an array.
        if (!is_array($_SESSION['csrftokens'])) {
            // $_SESSION['csrftokens'] is not an array! Reset/initialize $_SESSION['csrftokens'].
            $_SESSION['csrftokens'] = [];
            // It makes no sense to continue here, as we just cleaned the entire CSRF token basket.
            // Return, because we are done here.
            return;
        }

        // Walk through all the tokens.
        foreach ($_SESSION['csrftokens'] as $key => $token) {
            // all tokens are dot-separated with a timestamp and the actual random token
            // we start throwing away tokens here which either do not match this formatting,
            // or have been generated longer than the expiration time we just determined.

            $explodedToken = explode('.', $token, 2);

            // check if the token has a valid formatting (includes a timestamp)
            if (count($explodedToken) !== 2 || !is_numeric($explodedToken[0])) {
                // Can't parse this token. This should not happen... get rid of it!
                unset($_SESSION['csrftokens'][$key]);
                continue;
            }

            // check if the token is expired.
            if ($explodedToken[0] < (time() - $expiry)) {
                // Expired token found. Get rid of it!
                unset($_SESSION['csrftokens'][$key]);
                continue;
            }
        }
    }

    /**
     * Generate a session token and store it in the "token-basket".
     * We return the generated token here, which can be used to set the hidden form field.
     *
     * @return string
     */
    public static function generateToken()
    {
        // Generate the token based on the current time, plus a random string.
        $token = time() . '.';

        // Start with an empty string.
        $length       = 32;
        $characterset = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ01234567890';

        // For the desired length, keep adding random characters from the character set.
        for ($i = 0; $i < $length; $i++) {
            $token .= $characterset[mt_rand(0, strlen($characterset) - 1)];
        }

        $sessions = [];

        if (isset($_SESSION['csrftokens'])) {
            $sessions = array_slice($_SESSION['csrftokens'], -50, 50);
        }

        // Add the token to the "token-basket", a place in which we store all the currently valid CSRF tokens.
        $sessions[] = $token;

        $_SESSION['csrftokens'] = $sessions;

        return $token;
    }

    /**
     * Check if the given field is valid or not.
     * NOTE: this will also reset the value of the CSRF field.
     *
     * @return boolean
     */
    public function isValid()
    {
        // if this token was not validated before...
        if ($this->validated === null) {
            // get the token value
            $token = $this->field->getValue();

            if (!empty($_SESSION) && !empty($_SESSION['csrftokens'])) {
                // if the token is in the list of valid tokens, this field is valid!
                $key = array_search($token, $_SESSION['csrftokens']);
                if ($key !== false) {
                    // invalidate the token as it has already been used.
                    unset($_SESSION['csrftokens'][$key]);

                    $this->validated = true;
                } else {
                    $this->validated = false;
                }
            } else {
                $this->validated = false;
            }
        }

        return $this->validated;
    }
}

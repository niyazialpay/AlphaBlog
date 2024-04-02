<?php

return [
    'missing-input-secret' => 'The secret parameter was not passed.',
    'invalid-input-secret' => 'The secret parameter was invalid or did not exist.',
    'missing-input-response' => 'The response parameter was not passed.',
    'invalid-input-response' => 'The response parameter is invalid or has expired.',
    'invalid-widget-id' => 'The widget ID extracted from the parsed site secret key was invalid or did not exist.',
    'invalid-parsed-secret' => 'The secret extracted from the parsed site secret key was invalid.',
    'bad-request' => 'The request was rejected because it was malformed.',
    'timeout-or-duplicate' => 'The response parameter has already been validated before.',
    'internal-error' => 'An internal error happened while validating the response. The request can be retried.',
];

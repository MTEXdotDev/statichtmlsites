@php
$map = [
    403 => ['title' => 'Access Forbidden',         'msg' => 'You don\'t have permission to view this page.'],
    404 => ['title' => 'Page Not Found',            'msg' => 'The page or file you\'re looking for doesn\'t exist.'],
    419 => ['title' => 'Session Expired',           'msg' => 'Your session has expired. Please refresh and try again.'],
    429 => ['title' => 'Too Many Requests',         'msg' => 'You\'re doing that too fast. Please wait a moment.'],
    500 => ['title' => 'Server Error',              'msg' => 'Something went wrong on our end. We\'ve been notified.'],
    503 => ['title' => 'Service Unavailable',       'msg' => 'We\'re doing some maintenance. Back shortly!'],
];
$info = $map[404] ?? ['title' => 'Error', 'msg' => 'An unexpected error occurred.'];
@endphp
@include('errors.layout', ['code' => 404, 'title' => $info['title'], 'message' => $info['msg']])

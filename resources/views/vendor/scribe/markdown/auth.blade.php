# Authenticating requests

@if(!$isAuthed)
This API is not authenticated.
@else
<p>This API is authenticated by sending a parameter
    <strong><code>sign</code></strong>
    in the <b>body</b> or <b>query</b> of the request.
</p>
<p>All authenticated endpoints are marked with a
    <strong>requires authentication</strong> badge in the documentation below.
</p>
{!! $extraAuthInfo !!}
@endif
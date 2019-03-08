Title
=====

Here is a malformed table.

==========  ========================================  ==========================================
Route path  If the requested URL is ``/foo``          If the requested URL is ``/foo/``
----------  -----------------------------------  ------------------------------------------
``/foo``    It matches (``200`` status response)      It makes a ``301`` redirect to ``/foo``
``/foo/``   It makes a ``301`` redirect to ``/foo/``  It matches (``200`` status response)
==========  ========================================  ==========================================

And some text after!

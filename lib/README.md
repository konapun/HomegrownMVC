# Sample components which may be used with Homegrown
Although Homegrown's Contexts don't insist on anything in particular to provide
the view engine, request, or database handle, these sample implementations may
be used.

## HTTPRequest
An abstraction for `$_SERVER['REQUEST_URI']` which provides some useful methods
**DOCUMENTATION PENDING**

## Models
The sample framework provided defines two types of models:
  * plural: A model which you may query for a certain field and it will return a
    collection of singular type models. The plural model abstract class is
    written to be used with PDO.
  * singular: A model which is either returned from its plural model, or created
    on its own through the database handle and a field passed into the
    constructor.
    
TODO: documentaion. See each file's source for stubs

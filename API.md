**This is web.php API documentation.**

# Functions #

## get ##

get - Matches path with a HTTP GET request and executes passed function if a match is found

### Description ###

|void **get** ( string _$path_, mixed _$func_ )|
|:---------------------------------------------|

### Parameters ###

_path_

> |A route on which request URL path is compared.|
|:---------------------------------------------|

_func_

> |A function to execute when request URL path is matched to _$path_.|
|:-----------------------------------------------------------------|

### Examples ###

#### Example #1 Using get function to add routes ####

```
// Add a GET route using an anonymous function
get('/hello/%p', function($name) {
  echo "Hello, $name!"
});

// Add a GET route using a static method
get('/', 'Controller::index');

// Add a GET route using an instance method
get('/', 'Controller->index');

// Add a GET route using a file
get('/', 'controller.php');
```

### Notes ###

**Note:** This function is only used to collect the routes and the functions. By itself it does nothing, but the information collected is used with [dispatch()] and [forward()].

### See Also ###

  * [post()](API#post.md) - Add a HTTP POST request route to routes array with specific path and the controller function.
  * [put()](API#put.md) - Add a HTTP PUT request route to routes array with specific path and the controller function.
  * [delete()](API#delete.md) - Add a HTTP DELETE request route to routes array with specific path and the controller function.
  * [route()](API#route.md) - Add a any HTTP request route to routes array with specific path and the controller function.


---


## post ##

post - Add a HTTP POST request route to routes array with specific path and the controller function

### Description ###

|void **post** ( string _$path_, mixed _$func_ )|
|:----------------------------------------------|

### Parameters ###

_path_

> |A route on which request URL path is compared.|
|:---------------------------------------------|

_func_

> |A function to execute when request URL path is matched to _$path_.|
|:-----------------------------------------------------------------|

### Examples ###

#### Example #1 Using post function to add routes ####

```
// Add a POST route using an anonymous function
post('/', function() {
  echo 'Hello, World!'
});

// Add a POST route using a static method
post('/', 'Controller::index');

// Add a POST route using an instance method
post('/', 'Controller->index');
```

### Notes ###

**Note:** This function is only used to collect the routes and the functions. By itself it does nothing, but the information collected is used with [dispatch()] and [forward()].

### See Also ###

  * [get()](API#get.md) - Add a HTTP GET request route to routes array with specific path and the controller function.
  * [put()](API#put.md) - Add a HTTP PUT request route to routes array with specific path and the controller function.
  * [delete()](API#delete.md) - Add a HTTP DELETE request route to routes array with specific path and the controller function.
  * [route()](API#route.md) - Add a any HTTP request route to routes array with specific path and the controller function.
## Juncture\YtWrapper

The Google/Youtube API isn't the nicest thing in the world to work with.
Here's yet another Youtube API wrapper!


### Installation

Todo.


### Getting Started

Once installed, getting connected is easy. Instead of the dozen-plus lines required to get the Google and Youtube services set up, add this small snippet and you're good to go:
```php
Youtube::init((object) Config::get('google'));

if ( ! Youtube::setToken(Session::get('token')))
{
	return Redirect::to(Youtube::getAuthUrl());
}
```

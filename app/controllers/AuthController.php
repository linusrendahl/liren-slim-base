<?php

namespace App\Controllers;


/**
* 
*/
class AuthController
{

	protected $app;
	
	function __construct($app)
	{
		$this->app = $app;
	}



	public static function isAdmin()
	{

		if( ! \Cartalyst\Sentry\Facades\Native\Sentry::check()) {
			return false;
		}

		//get current user
		$user = \Cartalyst\Sentry\Facades\Native\Sentry::getUser();

	    // Get the user permissions
	    if ($user->hasAccess('admin'))
	    {
	    	return true;
	    } else {
	    	return false;
	    }
	}

	public static function getUserId()
	{
		$current_user = \App\Controllers\AuthController::getCurrentUser();
		return $current_user->id;
	}


	public static function getCurrentUser()
	{
		return \Cartalyst\Sentry\Facades\Native\Sentry::getUser();
	}


	public static function isLoggedIn()
	{
		return \Cartalyst\Sentry\Facades\Native\Sentry::check();
	}



	public function createUser()
	{


		try
		{
		    // Create the user
		    $user = \Cartalyst\Sentry\Facades\Native\Sentry::createUser(array(
		        'email'     => 'kalle@kalle.com',
		        'password'  => 'password',
		        'activated' => true,
		    ));

		    // Find the group using the group id
		    $adminGroup = \Cartalyst\Sentry\Facades\Native\Sentry::findGroupById(3);

		    // Assign the group to the user
		    $user->addGroup($adminGroup);
		}
		catch (Cartalyst\Sentry\Users\LoginRequiredException $e)
		{
		    echo 'Login field is required.';
		}
		catch (Cartalyst\Sentry\Users\PasswordRequiredException $e)
		{
		    echo 'Password field is required.';
		}
		catch (Cartalyst\Sentry\Users\UserExistsException $e)
		{
		    echo 'User with this login already exists.';
		}
		catch (Cartalyst\Sentry\Groups\GroupNotFoundException $e)
		{
		    echo 'Group was not found.';
		}
	}


	public function createGroup()
	{
		try
		{
		    // Create the group
		    $group = \Cartalyst\Sentry\Facades\Native\Sentry::createGroup(array(
		        'name'        => 'CourseAccess',
		        'permissions' => array(
		            'course.access' => 1
		        ),
		    ));
		}
		catch (Cartalyst\Sentry\Groups\NameRequiredException $e)
		{
		    echo 'Name field is required';
		}
		catch (Cartalyst\Sentry\Groups\GroupExistsException $e)
		{
		    echo 'Group already exists';
		}
	}


	public function authenticate($email_alias=null)
	{

		// Login credentials
	    $credentials = array(
	        'email'    => is_null($email_alias) ? $this->app->request->post('username') : $email_alias,
	        'password' => $this->app->request->post('password'),
	    );

	    $error_msg = '';

		try
		{
		    
		    // Get the Throttle Provider
			$throttleProvider = \Cartalyst\Sentry\Facades\Native\Sentry::getThrottleProvider();
			// Disable the Throttling Feature
			$throttleProvider->enable();

		    // Authenticate the user
		    $user = \Cartalyst\Sentry\Facades\Native\Sentry::authenticate($credentials, false);
		}


		catch (\Cartalyst\Sentry\Users\LoginRequiredException $e)
		{
		    $error_msg = 'Login field is required.';
		}
		catch (\Cartalyst\Sentry\Users\PasswordRequiredException $e)
		{
		    $error_msg = 'Password field is required.';
		}
		catch (\Cartalyst\Sentry\Users\WrongPasswordException $e)
		{
		    $error_msg = 'Wrong password, try again.';
		}
		catch (\Cartalyst\Sentry\Users\UserNotFoundException $e)
		{
		    $error_msg = 'User was not found.';
		}
		catch (\Cartalyst\Sentry\Users\UserNotActivatedException $e)
		{
		    $error_msg = 'User is not activated.';
		}

		// The following is only required if the throttling is enabled
		catch (\Cartalyst\Sentry\Throttling\UserSuspendedException $e)
		{
		    $error_msg = 'User is suspended.';
		}
		catch (\Cartalyst\Sentry\Throttling\UserBannedException $e)
		{
		    $error_msg = 'User is banned.';
		}

			
		return $error_msg;
	}


	public function check()
	{
		return \Cartalyst\Sentry\Facades\Native\Sentry::check();
	}


	public function logout()
	{
		\Cartalyst\Sentry\Facades\Native\Sentry::logout();
	}
}
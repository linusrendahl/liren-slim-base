<?php

namespace App\Controllers;
use \Cartalyst\Sentry\Facades\Native\Sentry;
use \Cartalyst\Sentry\Users\LoginRequiredException;
use \Cartalyst\Sentry\Users\UserNotFoundException;
use \Cartalyst\Sentry\Users\UserAlreadyActivatedException;

/**
* 
*/
class UserController
{

	protected $app;
	
	function __construct($app)
	{
		$this->app = $app;
	}

	/**
	 * Register a new User
	 * @param string $username (email)
	 * @param string password
	 */
	public function reqisterNewUser($username, $password, $user_group=null)
	{

		try
		{
		     // Let's register a user.
		    $user = Sentry::register(array(
		        'email'    => $username,
		        'password' => $password
		    ));

		    //Let's get the activation code
		    $activationCode = $user->getActivationCode();

		    //if we want to add this user to a group
		    if($user_group) {
		    	// Find the group using the group name
			    $userGroup = \Cartalyst\Sentry\Facades\Native\Sentry::findGroupByName($user_group);

			    // Assign the group to the user
			    $user->addGroup($userGroup);
		    }
		    

		}
		catch (LoginRequiredException $e)
		{
		    return false;
		}

		//send email with activation details.
		$this->newUserActivationEmail($username, $user_group);

		return true;
	}


	public function getUsersByGroup($group_name)
	{
		$group = Sentry::findGroupByName($group_name);
		$users = Sentry::findAllUsersInGroup($group);
		return $users;
	}

	/**
	 * Create a new user, used in admin interface.
	 * @param string $email
	 * @param string $password
	 * @param int boolean $active
	 * @param string $first_name
	 * @param string $last_name
	 * @return void
	 *
	 */
	public function createNewUser($email, $password, $user_group=null)
	{
		try
		{

		    // Create the user
		    $user = Sentry::createUser(array(
		        'email'     => $email,
		        'password'  => $password
		    ));

		    //if we want to add this user to a group
		    if($user_group) {
		    	// Find the group using the group name
			    $userGroup = \Cartalyst\Sentry\Facades\Native\Sentry::findGroupById($user_group);

			    // Assign the group to the user
			    $user->addGroup($userGroup);
		    }

		}
		catch (Cartalyst\Sentry\Users\LoginRequiredException $e)
		{
		    echo 'Login field is required.';
		}
		catch (Cartalyst\Sentry\Users\PasswordRequiredException $e)
		{
		    echo 'Password field is required.';
		}
		catch (\Cartalyst\Sentry\Users\UserExistsException $e)
		{
		    $this->app->flash('admin_error', 'Det finns redan en användare med denna Epostadress.');
		    $this->app->redirectTo('admin-users-create');
		}

	}

	/**
	 * Resets a User password
	 * @param string $username
	 * @return void
	 */
	public function resetUserPassword($username)
	{
		try
		{
		    // Find the user using the user email address
		    $user = Sentry::findUserByLogin($username);

		    // Get the password reset code
		    $resetCode = $user->getResetPasswordCode();

		    // Now you can send this code to your user via email for example.
		    $this->resetUserPasswordMail($username);
		}
		catch (Cartalyst\Sentry\Users\UserNotFoundException $e)
		{
		   die('Användaren hittades inte.');
		}
	}

	/**
	 * Send email with user reset password
	 * @param string $username
	 */
	public function resetUserPasswordMail($username)
	{
		$user = \App\Models\User::where('email', '=', $username)->first();

		$link = 'http://' . $this->app->request->getHost() . $this->app->urlFor('new-password-user', ['user_id' => $user->id, 'reset_password_code' => $user->reset_password_code]);

		$this->app->mail->subject('Dubetydermer.se: Ändra lösenord.')
			->to([$username])
			->from(['site@dubetydermer.se' => 'Du Betyder Mer'])
			->body('Klicka på följande länk för att skapa ett nytt lösenord: '.$link.'', 'text/html')
			->send();
	}


	/**
	 * Enter a new password with the Reset User Password function
	 * @param int @user_id
	 * @param string $reset_password_code
	 */
	public function setNewPasswordReset($user_id, $reset_password_code, $new_password)
	{
		try
		{
		    // Find the user using the user id
		    $user = Sentry::findUserById($user_id);

		    // Check if the reset password code is valid
		    if ($user->checkResetPasswordCode($reset_password_code))
		    {
		        // Attempt to reset the user password
		        if ($user->attemptResetPassword($reset_password_code, $new_password))
		        {
		            // Password reset passed
		            $this->app->flashNow('info', array(
						'msg'	=> '<i class="fa fa-info-circle"></i> Ditt lösenord har ändrats.',
						'class'	=> 'bg-success'
						));
		        }
		        else
		        {
		            // Password reset failed
		            $this->app->flashNow('info', array(
						'msg'	=> '<i class="fa fa-info-circle"></i> Ditt lösenord gick inte att ändra. Vänligen kontakta vår support om problemet kvarstår.',
						'class'	=> 'bg-danger'
						));
		        }
		    }
		    else
		    {
		        // The provided password reset code is Invalid
		        $this->app->flashNow('info', array(
						'msg'	=> '<i class="fa fa-info-circle"></i> Ditt lösenord gick inte att ändra. Vänligen kontakta vår support om problemet kvarstår.',
						'class'	=> 'bg-danger'
						));
		    }
		}
		catch (Cartalyst\Sentry\Users\UserNotFoundException $e)
		{
		    echo 'User was not found.';
		}
	}

	/**
	 * Change a Users password, currently used for activation of account.
	 * @param int $user_id
	 * @return bool
	 */
	public function setNewPassword($user_id, $new_password)
	{

		try {
			$user = Sentry::findUserById($user_id);
		} catch (\Cartalyst\Sentry\Users\UserNotFoundException $e) {
			return false;
		}
		
	    $user->password = $new_password;
	    $user->save();

	    return true;

	}


	public function activateUser($user_id, $key)
	{
		try
		{
		    // Find the user using the user id
		    $user = Sentry::findUserById($user_id);

		    // Attempt to activate the user
		    if ($user->attemptActivation($key))
		    {
		        // User activation passed.
		        return true;
		    }
		    else
		    {
		        // User activation failed
		        return false;
		    }
		}
		catch (UserNotFoundException $e)
		{
		    return false;
		}
		catch (UserAlreadyActivatedException $e)
		{
		    return false;
		}

		return true;
	}

	public function newUserActivationEmail($username, $user_group)
	{
		$user = \App\Models\User::where('email', '=', $username)->first();

		if($user_group == 'school') {
			$link = 'http://' . $this->app->request->getHost() . $this->app->urlFor('activate-user-school', ['user_id' => $user->id, 'key' => $user->activation_code]);
		} else if($user_group == 'sponsor') {
			$link = 'http://' . $this->app->request->getHost() . $this->app->urlFor('activate-user-sponsor', ['user_id' => $user->id, 'key' => $user->activation_code]);
		} else {
			$link = 'http://' . $this->app->request->getHost() . $this->app->urlFor('activate-user', ['user_id' => $user->id, 'key' => $user->activation_code]);
		}

		//link for activating a gift account
		$link_gift = 'http://' . $this->app->request->getHost() . $this->app->urlFor('activate-user-gift', ['user_id' => $user->id, 'key' => $user->activation_code]);
		
		//send email depending on User Group (private, school, sponsor)
		$email = \App\Models\Emails::where('identifier', '=', $user_group)->first();

		//replace shortcode for user activation link
		$content = str_replace('[activation_link]', $link, $email->content);
		//replace shortcode for user activation as a gift for someone else link
		$content = str_replace('[activation_link_gift]', $link_gift, $content);

		//send mail to new user
		$this->app->mail->subject($email->subject)
			->to([$username])
			->from([$email->from_email => $email->from_mail])
			->body($content, 'text/html')
			->send();

		//send mail to self that new user has been registered
		$this->app->mail->subject('DBM: Ny användare har registrerats.')
			->to(['info@dubetydermer.se'])
			->from(['site@dubetydermer.se' => 'DBM'])
			->body('Ny användare med användarnamn ' . $username . ' har registrerats.', 'text/html')
			->send();
	}



	/**
	 * Give a user access to the course
	 * @param int $user_id
	 * @return void
	 */
	public function activateCourseAccess()
	{

		$user_id = \App\Controllers\AuthController::getCurrentUser()->id;

		//find user
		$user = Sentry::findUserById($user_id);

		// Find the group using the group id
	    $course_group = Sentry::findGroupByName('CourseAccess');

	    // Assign the group to the user
	    $user->addGroup($course_group);

	    return;
	}

	public function hasCourseAccess($user_id)
	{
		$user = Sentry::findUserById($user_id);

		$course_duration = 31536000; //365 days
		$activation_time = strtotime($user->activated_at);
		$subscr_ends = $activation_time + $course_duration;
		$time_left = $subscr_ends - time();
		
		return ($time_left > 0) ? true : false;
	}

	/**
	 * Redirect user to a specific course
	 */
	public function redirectToCourse()
	{
		//get current user account type
		$account_type = $this->app->user->getAccountType();

		//create slug based on account type
		switch ($account_type) {
			case 'school':
				# code...
				$course_slug = 'tobak-skola';
				break;

			case 'private':
				# code...
				$course_slug = 'tobak-privat';
				break;

			case 'admin':
				# code...
				$this->app->redirectTo('select-course-admin');
				break;

			case 'sponsor':
				$this->app->flash('info', [
						'msg'	=> '<i class="fa fa-info-circle"></i> Du har inte tillgång till kursen.',
						'class'	=> 'bg-danger'
					]);
				$this->app->redirectTo('home');
				break;
			
			default:
				# code...
				die('Unknown account type.');
				break;
		}
		
		$this->app->redirectTo('the-course-intro', array('course_slug' => $course_slug));
	}


	public function getAccountType()
	{

		$user_id = \App\Controllers\AuthController::getUserId();
		
		try
		{
		    // Find the user using the user id
		    $user = Sentry::findUserByID($user_id);

		    // Get the user groups
		    $groups = $user->getGroups();

		    //return group name assigned to user
		    return strtolower($groups[0]->name);
		}
		catch (Cartalyst\Sentry\Users\UserNotFoundException $e)
		{
		    //echo 'User was not found.';
		}

		return false;

	}

	public function getAccountTypeNotLoggedIn($user_id)
	{
		$user_id = \App\Controllers\AuthController::getUserId();
		
		try
		{
		    // Find the user using the user id
		    $user = Sentry::findUserByID($user_id);

		    // Get the user groups
		    $groups = $user->getGroups();

		    //return group name assigned to user
		    return strtolower($groups[0]->name);
		}
		catch (Cartalyst\Sentry\Users\UserNotFoundException $e)
		{
		    echo 'User was not found.';
		}

		return false;
	}


}
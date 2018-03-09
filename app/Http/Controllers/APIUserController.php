<?php

namespace App\Http\Controllers;

use App\Models\Passenger;
use Dirape\Token\Token;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

/**
 * @resource Passenger API Controller
 *
 */
class APIUserController extends Controller {
	/**
	 * @param  string $email The email of the user
	 * @param  string $username The username of the user
	 * @param  string $password The password of the user
	 *
	 * @return array
	 */
	public function signUp( Request $request ) {
		$email     = $request->input( 'email' );
		$password  = $request->input( 'password' );
		$username  = $request->input( 'username' );
		$validator = Validator::make( $request->toArray(), [
			'username' => 'required|unique:passengers',
			'password' => 'required',
			'email'    => 'required|unique:passengers'
		] );
		$response  = array( 'response' => [], 'success' => true, 'token' => '' );
		if ( $validator->fails() ) {
			$response['response'] = $validator->messages();
			$response['success']  = false;
		} else {
			$token               = new Token();
			$passenger           = new Passenger();
			$passenger->username = $username;
			$passenger->password = bcrypt( $password );
			$passenger->email    = $email;
			$passenger->token    = $token->Unique( 'passengers', 'token', 10 );
			$response['token']   = $passenger->token;
			$passenger->save();
		}

		return response()->json( $response );
	}

	/**
	 * @param  string $username The username of the user
	 * @param  string $password The password of the user
	 *
	 * @return array
	 */
	public function signIn( Request $request ) {
		$username  = $request->input( 'username' );
		$password  = $request->input( 'password' );
		$validator = Validator::make( $request->toArray(), [
			'username' => 'required|exists:passengers',
			'password' => 'required',
		] );
		$response  = array( 'response' => [], 'success' => true, 'token' => '' );
		if ( $validator->fails() ) {
			$response['response'] = $validator->messages();
			$response['success']  = false;

			return response()->json( $response );
		}
		$passenger = Passenger::where( [ 'username' => $username ] )->first();
		if ( ! Hash::check( $password, $passenger->password ) ) {
			$response['response']['password'] = "Password don't match";
			$response['success']              = false;
		} else {
			$response['token'] = Passenger::where( 'username', $username )->first()->token;
		}

		return response()->json( $response );
	}

	/**
	 * @param  string $email The email of the user
	 *
	 * @return array
	 */
	public function resetPassword( Request $request ) {
		$npass = Hash::make( str_random( 6 ) );

		$data                = "Your New Password is" . $npass;
		$passenger           = Passenger::find( $request->input( 'email' ) );
		$passenger->password = $npass;
		$passenger->update();

		Mail::send( 'emails.send', [
			'title' => "Password Reset",
			$data,
			function ( $message ) use ( $request ) {

				$message->from( 'mokhtarashrakat@gmail.com', 'imobuts' );

				$message->to( $request->input( 'email' ) );
			}
		] );

		return response()->json( [ 'message' => 'Email sent to you with a new password' ] );

	}

	/**
	 * @param  string $token The unique token of the user
	 * @param  string $email The email of the user
	 * @param  string $username The username of the user
	 * @param  string $password The password of the user
	 *
	 * @return array
	 */
	public function updateProfile( Request $request ) {
		$email     = $request->input( 'email' );
		$password  = $request->input( 'password' );
		$username  = $request->input( 'username' );
		$validator = Validator::make( $request->toArray(), [
			'username' => 'required|unique:passengers',
			'password' => 'required',
			'email'    => 'required|unique:passengers'
		] );
		$response  = array( 'response' => [], 'success' => true );
		if ( $validator->fails() ) {
			$response['response'] = $validator->messages();
			$response['success']  = false;
		} else {
			$passenger           = Passenger::find( $request->input( 'token' ) );
			$passenger->username = $username;
			$passenger->email    = $email;
			$passenger->password = bcrypt( $password );
			$passenger->update();
		}

		return response()->json( $response );
	}

	/**
	 * @param  string $token The unique token of the user
	 * @return array
	 */
	public function getProfile( Request $request ) {
		$passenger = Passenger::find( $request->input( 'token' ) );
		$response  = array( 'response' => [], 'success' => true );
		if ( $passenger ) {
			$response['response'] = $passenger;
		} else {
			$response['success'] = false;
		}

		return response()->json( $response );

	}
}
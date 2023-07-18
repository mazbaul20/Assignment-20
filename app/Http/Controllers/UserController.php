<?php

namespace App\Http\Controllers;

use Exception;
use App\Models\User;
use App\Mail\OTPMail;
use App\Helper\JWTToken;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class UserController extends Controller
{
    function UserRegistration(Request $request){
        try{
            User::create([
                'firstName' => $request->input('firstName'),
                'lastName' => $request->input('lastName'),
                'email' => $request->input('email'),
                'mobile' => $request->input('mobile'),
                'password' => $request->input('password')
            ]);
            return response()->json([
                'status' => 'success',
                'message' => 'User Registraion Successfully'
            ],200);
        }catch(Exception $e){
            return response()->json([
                'status' => 'success',
                'message' => 'User Registraion Failled'
            ],200);
        }
        
    } // end method

    function UserLogin(Request $request){
        $count = User::where('email','=',$request->input('email'))
        ->where('password','=',$request->input('password'))
        ->count();

        if($count == 1){
            // User Login->JWT Token Issue
            $token = JWTToken::CreateToken($request->input('email'));
            return response()->json([
                'status' => 'success',
                'message' => 'User Login Successful',
                'token' => $token
            ]);

        }else{
            return response()->json([
                'status' => 'failled',
                'message' => 'unauthorized'
            ],200);
        }
    }// end method

    function SendOTPCode(Request $request){
        
        $email = $request->input('email');
        $otp = rand(1000,9999);
        $count=User::where('email','=',$email)->count();

        if($count == 1){
            // OTP Email Address
            //OTP Code Inaert
            Mail::to($email)->send(new OTPMail($otp));

            User::where('email','=',$email)->update(['otp'=>$otp]);

            return response()->json([
                'status'=>'success',
                'message'=> '4 Digit OTP Code has been send to your email !'
            ],200);
        }else{
            return response()->json([
                'status'=>'failed',
                'message'=> 'unauthorized'
            ]);
        }
    }// end method

    function VerifyOTP(Request $request){
        $email = $request->input('email');
        $otp = $request->input('otp');
        $count = User::where('email','=',$email)
            ->where('otp','=',$otp)->count();

        if($count==1){
            // Database OTP Update
            User::where('email','=',$email)->update(['otp'=>'0']);

            // Password Reset Token Issue
            $token = JWTToken::CreateTokenForSetPassword($request->input('email'));
            return response()->json([
                'status'=>'success',
                'message'=> 'OTP Verification Successful',
                'token' => $token
            ],200);
            
        }else{
            return response()->json([
                'status'=>'failed',
                'message'=> 'unauthorized'
            ]);
        }
    }
}

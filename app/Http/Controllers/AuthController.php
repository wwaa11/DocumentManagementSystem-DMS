<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\StaffApiClient;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function __construct(private StaffApiClient $staffApi) {}

    public function Login(): View
    {
        return view('layouts.login');
    }

    public function LoginRequest(Request $req): JsonResponse
    {
        $userid = $req->userid;
        $data = [
            'status' => 'failed',
            'message' => null,
        ];

        if (config('app.env') == 'local') {
            $data['status'] = 'success';
            $data['message'] = 'เข้าสู่ระบบสำเร็จ';

            $userData = User::where('userid', $userid)->first();
            if ($userData == null) {
                $response = $this->staffApi->getUser($userid);

                if ($response->successful()) {
                    $responseData = $response->json();
                    if ($responseData['status'] == 1) {
                        $userData = new User;
                        $userData->userid = $userid;
                        $userData->name = $responseData['user']['name'];
                        $userData->position = $responseData['user']['position'];
                        $userData->department = $responseData['user']['department'];
                        $userData->division = $responseData['user']['division'];
                        $userData->email = $responseData['user']['email'];
                        $userData->role = $this->setRoles($responseData['user']['department'], $responseData['user']['division']);
                        $userData->save();
                    }
                }
            }
            Auth::login($userData);

            return response()->json($data, 200);
        }

        try {
            $response = $this->staffApi->authenticate($userid, (string) $req->password);

            if (! $response->successful()) {
                $data['message'] = 'ไม่สามารถเชื่อมต่อกับระบบได้ กรุณาลองใหม่อีกครั้ง';

                return response()->json($data, 200);
            }
            $responseData = $response->json();

            if (! isset($responseData['status'])) {
                $data['message'] = 'ข้อมูลที่ได้รับจากระบบไม่ถูกต้อง';

                return response()->json($data, 200);
            }
        } catch (\Exception $e) {
            $data['message'] = 'เกิดข้อผิดพลาดในการเชื่อมต่อ กรุณาลองใหม่อีกครั้ง';

            return response()->json($data, 200);
        }

        $data['message'] = 'ไม่พบรหัสพนักงานนี้';

        if ($responseData['status'] == 1) {
            $userData = User::where('userid', $req->userid)->first();
            if (! $userData) {
                $userData = new User;
                $userData->userid = $userid;
                $userData->role = $this->setRoles($responseData['user']['department'], $responseData['user']['division']);
            }
            $userData->name = $responseData['user']['name'];
            $userData->position = $responseData['user']['position'];
            $userData->department = $responseData['user']['department'];
            $userData->division = $responseData['user']['division'];
            $userData->email = $responseData['user']['email'];
            $userData->save();

            Auth::login($userData);

            $data['status'] = 'success';
            $data['message'] = 'เข้าสู่ระบบสำเร็จ';
        }

        return response()->json($data, 200);
    }

    private function setRoles(string $department, string $division): string
    {
        if ($division == 'ฝ่ายเทคโนโลยีสารสนเทศ') {
            return 'it';
        }
        if ($department == 'แผนกห้องปฏิบัติการ') {
            return 'lab';
        }
        if ($department == 'แผนกเอกซเรย์') {
            return 'pac';
        }
        if ($department == 'แผนก Contact Center') {
            return 'heartstream';
        }
        if ($department == 'แผนกRegistration') {
            return 'register';
        }
        if ($department == 'แผนกสื่อสารแบรนด์และสื่อสารการตลาด') {
            return 'media';
        }
        if ($division == 'ฝ่ายจัดซื้อ') {
            return 'purchase';
        }

        return 'user';
    }

    public function LogoutRequest(Request $request): JsonResponse
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return response()->json([
            'status' => 'success',
            'message' => 'ออกจากระบบสำเร็จ',
        ], 200);
    }
}

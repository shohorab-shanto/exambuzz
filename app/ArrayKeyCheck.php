<?php

use App\Models\Exam;
use App\Models\PreliminaryAnswer;
use App\Models\WrittenAnswer;
use Illuminate\Support\Facades\DB;

function multiKeyExists($arr, $key) {

    if (!is_array($arr)) {
        $arr = explode(':', $arr);
    }

    if (array_key_exists($key, $arr)) {
        return true;
    }

    return false;
}

function getPreli($id) {
    $data = Exam::find($id) ?? [];

    return $data;
}

function liveExamCount($user_id) {
    $data    = [];
    $p_count = PreliminaryAnswer::where('user_id', $user_id)
        ->join('exams', 'exams.id', 'preliminary_answers.exam_id')
        ->whereDate('exams.expired_at', '>=', 'preliminary_answers.created_at')
        ->count();
    $w_count = WrittenAnswer::where('user_id', $user_id)
        ->join('writtens', 'writtens.id', 'written_answers.written_id')
        ->whereDate('writtens.expired_at', '>=', 'written_answers.created_at')
        ->count();
    $data['p_count'] = $p_count;
    $data['w_count'] = $w_count;

    return $data;
}

function sendSMS($phone, $otp) {
    $to      = $phone;
    $token   = "10312213457170187689785afbc1f9a74ff9c04faa745a13d7212";
    $message = "Your OTP is " . $otp;

    $url = "http://api.greenweb.com.bd/api.php?json";

    $data = [
        'to'      => "$to",
        'message' => "$message",
        'token'   => "$token",
    ];
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
    curl_setopt($ch, CURLOPT_ENCODING, '');
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_exec($ch);
}

function getPaperTeacher($written_id) {
    $data                     = [];
    $data['assigned']         = WrittenAnswer::where('written_id', $written_id)->whereNotNull('teacher_id')->count();
    $data['assigned_teacher'] = WrittenAnswer::where('written_id', $written_id)->whereNotNull('teacher_id')->groupBy('teacher_id')->count();

    return $data;
}

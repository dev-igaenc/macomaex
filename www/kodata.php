<?php
include_once('/home/hosting_users/ice_macomaex/www/common.php');
// Composer 오토로더 포함
require '/home/hosting_users/ice_macomaex/www/vendor/autoload.php';

// phpseclib 클래스 임포트
use phpseclib3\Net\SFTP;
use phpseclib3\Crypt\PublicKeyLoader; // 키 파일 사용 시 필요
use phpseclib3\Exception\UnableToConnectException;
use phpseclib3\Exception\AuthenticationFailedException;

// --- SFTP 접속 정보 및 파일 경로 설정 ---
// ※※※ 아래 값들을 실제 환경에 맞게 수정해주세요 ※※※
$sftpHost = function_exists('macomaex_env') ? macomaex_env('KODATA_SFTP_HOST', '') : '';
$sftpPort = function_exists('macomaex_env') ? (int) macomaex_env('KODATA_SFTP_PORT', '22') : 22;
$sftpUser = function_exists('macomaex_env') ? macomaex_env('KODATA_SFTP_USER', '') : '';

// --- 인증 방식 선택 ---
// 1. 비밀번호 인증 (이 방식을 사용하려면 아래 $sftpPass 변수 설정)
$sftpPass = function_exists('macomaex_env') ? macomaex_env('KODATA_SFTP_PASSWORD', '') : '';

// 2. 키 파일 인증 (이 방식을 사용하려면 아래 $privateKeyPath 설정, $sftpPass는 주석처리하거나 비워두세요)
// $privateKeyPath = '/path/to/your/private_key.pem'; // 예: /home/user/.ssh/id_rsa
// $keyPassword = null; // 프라이빗 키에 암호가 설정된 경우 입력, 없으면 null 또는 false

// --- 다운로드할 파일 정보 ---
$today = date("Ymd");
$file_name='igaencftp_'.$today.'.jar';
$remoteFilePath = '/home/igaencftp/enpinfo/'.$today.'/'.$file_name; // SFTP 서버에 있는 파일 경로
$localFilePath = '/home/hosting_users/ice_macomaex/www/data/kodata/'.$file_name;                // 로컬에 저장할 경로 및 파일명
// ※※※ 설정 끝 ※※※

// SFTP 객체 생성
$sftp = new SFTP($sftpHost, $sftpPort);

try {
    echo "[INFO] Connecting to {$sftpHost}:{$sftpPort}...<br>";
    // 실제 연결 및 인증은 login() 메소드에서 시도됩니다.

    echo "[INFO] Authenticating as user '{$sftpUser}'...<br>";

    // --- 인증 시도 ---
    $loggedIn = false;

    // 키 파일 인증 우선 시도 (경로가 설정된 경우)
    if (isset($privateKeyPath) && !empty($privateKeyPath)) {
        if (!file_exists($privateKeyPath)) {
            throw new \Exception("[ERROR] Private key file not found at: {$privateKeyPath}");
        }
        try {
            echo "[INFO] Attempting key-based authentication...<br>";
            // 키 로드 (PHP 7.4 이상에서는 ?? 연산자 사용 가능)
            $privateKey = PublicKeyLoader::load(file_get_contents($privateKeyPath), $keyPassword ?? false);
            if ($sftp->login($sftpUser, $privateKey)) {
                $loggedIn = true;
                echo "[SUCCESS] Key authentication successful.<br>";
            } else {
                 echo "[WARN] Key authentication failed. Trying password authentication if available...<br>";
            }
        } catch (\Exception $e) {
            // 키 로딩 실패 등
            echo "[WARN] Key loading/authentication failed: " . $e->getMessage() . "<br>";
            // 키 인증 실패 시 비밀번호 인증 시도 (선택적)
        }
    }

    // 비밀번호 인증 시도 (키 인증을 사용하지 않았거나 실패했고, 비밀번호가 설정된 경우)
    if (!$loggedIn && isset($sftpPass) && !empty($sftpPass)) {
        echo "[INFO] Attempting password authentication...<br>";
        if ($sftp->login($sftpUser, $sftpPass)) {
            $loggedIn = true;
            echo "[SUCCESS] Password authentication successful.<br>";
        }
    }

    // 최종 인증 실패 확인
    if (!$loggedIn) {
         throw new AuthenticationFailedException("[ERROR] Authentication failed for user '{$sftpUser}'. Check credentials/key or server logs.");
    }

    // --- 파일 다운로드 ---
    echo "[INFO] Attempting to download remote file:<br>";
    echo "       From: {$remoteFilePath}<br>";
    echo "       To:   {$localFilePath}<br>";

	/*
	$files = $sftp->nlist("/home/igaencftp/enpinfo/");
    foreach ($files as $file) {
        echo $file . "<br>";
    }
	*/
    // get() 메소드는 성공 시 true, 실패 시 false 반환
    if ($sftp->get($remoteFilePath, $localFilePath)) {
        // 다운로드 성공 후 파일 크기 확인 (선택적)
        if (file_exists($localFilePath)) {
             $fileSize = filesize($localFilePath);
			 $chk=sql_fetch("select * from company_update where FILE='".$file_name."'");
			
			 if(!$chk['c_id']){
				$sql = " insert company_update
							set FILE = '$file_name',
							ACCT_DT=now()
							 ";
				sql_query($sql);
			 }
		
			if($chk['STATUS']!="S"){



			}

             echo "[SUCCESS] File downloaded successfully. Local file size: " . number_format($fileSize) . " bytes.<br>";
        } else {
             echo "[SUCCESS] File download reported success, but local file could not be verified!<br>";
        }
    } else {
        // 다운로드 실패
        echo "[ERROR] File download failed.<br>";
        // 추가 오류 정보 확인
        $errors = $sftp->getSFTPErrors();
        if (!empty($errors)) {
            echo "       SFTP Errors:<br>";
            foreach ($errors as $error) {
                echo "       - " . $error . "<br>";
            }
        }
         $lastError = $sftp->getLastError();
         if ($lastError) {
             echo "       Last Error Message: " . $lastError . "<br>";
         }
         // 실패 시 생성된 0바이트 파일 삭제 시도
         if (file_exists($localFilePath) && filesize($localFilePath) === 0) {
            @unlink($localFilePath);
            echo "[INFO] Deleted empty local file created during failed download attempt.<br>";
         }
         // 여기서 예외를 던져 스크립트를 중단시킬 수도 있습니다.
         // throw new \Exception("File download failed.");
    }

} catch (UnableToConnectException $e) {
    echo "[FATAL] Connection failed: Could not connect to {$sftpHost}:{$sftpPort}.<br>";
    echo "        Error: " . $e->getMessage() . "<br>";
    exit(1); // 오류 종료 코드
} catch (AuthenticationFailedException $e) {
     echo "[FATAL] Authentication failed.<br>";
     echo "        Error: " . $e->getMessage() . "<br>";
     exit(1);
} catch (\Exception $e) {
    // 기타 예외 처리 (파일 경로 오류, 권한 문제 등 포함될 수 있음)
    echo "[FATAL] An unexpected error occurred:<br>";
    echo "        Error: " . $e->getMessage() . "<br>";
    exit(1);
} finally {
     // phpseclib는 객체 소멸 시 자동으로 연결을 해제하지만, 명시적 표기를 위해 주석 추가

}

?>

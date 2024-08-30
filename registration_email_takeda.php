<?php
include_once 'include/global.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;

require 'admin/vendor/autoload.php';

$conf_code = isset($conferences_code) ? $conferences_code : $_POST['conf_code'];
if (!is_dir('registration_mail_ics')) {
    mkdir('registration_mail_ics');
}
// echo $conf_code;
if($conf_code == "TKD26SEP")
{
    $even_location_tmp = "8 Nguyễn Bỉnh Khiêm, Phường Đa Kao, Quận 1, Thành phố Hồ Chí Minh.";
    $event_start_tmp = '20240926T173000'; // Định dạng: YYYYMMDDTHHMMSSZ
    $event_end_tmp = '20240926T213000';   // Định dạng: YYYYMMDDTHHMMSSZ
    $ics_file_tmp = 'registration_mail_ics/appointment_26.ics';
    //  MAIL
    $info_mail_tmp = 'Hội thảo được phối hợp tổ chức giữa Công ty TNHH Dược phẩm Takeda Việt Nam và Viện Pasteur TPHCM.';
    $time_mail_tmp = '17:30 – 21:30 ngày 26/09/2024.';
    $address_mail_tmp = 'Tại GEM Center Hồ Chí Minh';
    $lable_link_tmp = '8 Nguyễn Bỉnh Khiêm, Phường Đa Kao, Quận 1, Thành phố Hồ Chí Minh.';
    $link_edit_tmp = 'https://vnconference.icu/registration_update_tkd.php?confCode='.$conf_code;

    $link_tmp = 'https://maps.app.goo.gl/Thb2WSbBxhEHuJRG6';

    $subject_tmp = "(26/09/2024)";

    $link_img_tmp = "registration_mail_img/hcm.png";
}
else if($conf_code == "TKD27SEP"){
    $even_location_tmp = "Tầng 6, Crystal Grand Ballroom, 54 Liễu Giai, Cống Vị, Ba Đình, Hà Nội";
    $event_start_tmp = '20240927T173000'; // Định dạng: YYYYMMDDTHHMMSSZ
    $event_end_tmp = '20240927T213000';   // Định dạng: YYYYMMDDTHHMMSSZ
    $ics_file_tmp = 'registration_mail_ics/appointment_27.ics';
    //  MAIL
    $info_mail_tmp = 'Hội thảo được phối hợp tổ chức giữa Công ty TNHH Dược phẩm Takeda Việt Nam và Hội Y học dự phòng Việt Nam';
    $time_mail_tmp = '17:30 – 21:30 ngày 27/09/2024.';
    $address_mail_tmp = 'Tại Khách sạn Lotte Hà Nội';
    $lable_link_tmp = 'Tầng 6, Crystal Grand Ballroom, 54 Liễu Giai, Cống Vị, Ba Đình, Hà Nội';

    $link_edit_tmp = 'https://vnconference.icu/registration_update_tkd.php?confCode='.$conf_code;

    $link_tmp = 'https://maps.app.goo.gl/BEBvZFVnW3VFqB7M9';

    $subject_tmp = "(27/09/2024)";

    $link_img_tmp = "registration_mail_img/hn.png";
}
$sql_mail = "SELECT * FROM `vnc_mailsmtp`";
$row_mail = $db->rawQueryOne($sql_mail);

$row_conf = $db->rawQueryOne('SELECT * FROM `conferences` WHERE `conferences_code` = "' . $conf_code . '"');

$delegate_id = isset($delegate) ? $delegate : $_POST['delegate_id'];
$delegate_password = isset($_SESSION['delegate_password']) ? $_SESSION['delegate_password'] : '';
$row_delegate = $db->rawQueryOne('SELECT * FROM `delegate` WHERE `delegate_id` = "' . $delegate_id . '"');

$row_del_conf = $db->rawQueryOne("SELECT `delegate_firstname`,`delegate_conf_code`,`delegate`.`delegate_id`	
    FROM `delegate_conf` 
    INNER JOIN `delegate` ON `delegate_conf`.`delegate_id`=`delegate`.`delegate_id`
    INNER JOIN `conferences` ON `delegate_conf`.`conf_id`=`conferences`.`conferences_id`
    WHERE `delegate_conf`.`conf_id`='" . $row_conf['conferences_id'] . "' AND `delegate`.`delegate_id`='$delegate_id' ");
$filepath = 'admin/temp/';
$filename = $row_del_conf['delegate_conf_code'] . '_' . seoname($row_delegate['delegate_firstname']) . '.jpg';
$filepath .= $filename;

$event_title = 'VẮC-XIN: VŨ KHÍ MỚI TRONG DỰ PHÒNG SỐT XUẤT HUYẾT DENGUE TẠI VIỆT NAM';
$event_description = 'VẮC-XIN: VŨ KHÍ MỚI TRONG DỰ PHÒNG SỐT XUẤT HUYẾT DENGUE TẠI VIỆT NAM';
$event_location = $even_location_tmp; // Địa điểm tổ chức sự kiện
$event_start = $event_start_tmp; // Định dạng: YYYYMMDDTHHMMSSZ
$event_end = $event_end_tmp;   // Định dạng: YYYYMMDDTHHMMSSZ
$timezone = 'Asia/Ho_Chi_Minh';
$organizer_name = 'TAKEDA';
$organizer_email = "";
$ics_content = "BEGIN:VCALENDAR\r\n";
$ics_content .= "VERSION:2.0\r\n";
$ics_content .= "PRODID:-//Your Company//NONSGML Event//EN\r\n";
$ics_content .= "BEGIN:VEVENT\r\n";
$ics_content .= "METHOD:PUBLISH\r\n";
$ics_content .= "METHOD:REQUET\r\n";
$ics_content .= "X-MS-OLK-FORCEINSPECTOROPEN:TRUE\r\n";
$ics_content .= "X-WR-CALNAME:$event_title\r\n";
$ics_content .= "UID:uid1@example.com\r\n";
$ics_content .= "DTSTAMP:20240820T120000Z\r\n";
$ics_content .= "DTSTART;TZID=$timezone:$event_start\r\n";
$ics_content .= "DTEND;TZID=$timezone:$event_end\r\n";
$ics_content .= "SUMMARY:$event_title\r\n";
$ics_content .= "DESCRIPTION:$event_description\r\n";
$ics_content .= "LOCATION:$event_location\r\n";
$ics_content .= "ORGANIZER;CN=$organizer_name:mailto:$organizer_email\r\n";
$ics_content .= "END:VEVENT\r\n";
$ics_content .= "END:VCALENDAR\r\n";
$ics_file = $ics_file_tmp;
if (!file_exists($ics_file)) {
    if (file_put_contents($ics_file, $ics_content) === false) {
        die("Failed to create ICS file.");
    }
}


$mail = new PHPMailer;
$mail->isSMTP();
$mail->SMTPDebug = 0;
$mail->Debugoutput = 'html';
$mail->CharSet = "utf-8";
$mail->WordWrap = 50;
$mail->IsHTML(true);
$mail->Host = $row_mail['mail_host'];
$mail->Port = $row_mail['mail_port'];
$mail->SMTPSecure = $row_mail['mail_secure'];
$mail->SMTPAuth = true;
$mail->Username = 'admin@vnconference.net';
$mail->Password = 'mmfkfmdevkthxbrf';
$mail->From = $row_mail['mail_username'];
$mail->FromName = $row_conf['conferences_name'];
$mail->setFrom($row_mail['mail_username'], $row_conf['conferences_organization']);
$mail->AddAddress($row_delegate['delegate_email'], $row_delegate['delegate_firstname']);
//$mail->AddAddress('chieudv@hpu.edu.vn','Do Van Chieu');
// $mail->addReplyTo('leonguyen76@gmail.com', 'VNC Customer Service');

$mail->Subject = 'TAKEDA - Cảm ơn và QR CODE check-in vào hội nghị ' . $subject_tmp;

// Tạo mã HTML cho nội dung email
$mail->Body = '
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Email Template</title>
</head>
<body style="margin: 0; padding: 0; font-family: Arial, sans-serif;">
    <div style="padding: 20px;">
        <div style="background: #ea13ac;border-radius: 10px;color:white;padding:10px;">
            <h2 style="text-align:center;">Đây là <strong>QR CODE</strong> dùng để <strong>CHECK-IN</strong> tại hội nghị.</h2>
        </div>
        <div style="flex: 1; text-align: center; margin: 20px;">
            <img src="cid:' . seoname($row_delegate['delegate_firstname']) . '" alt="QR Code"/>
            '. ($delegate_password!=''?'<p><strong style="font-size:22px;">Password: '.$delegate_password.'</strong></p>':'') .'
        </div>
        <div>
            <p style="font-size:18px;text-align: center; margin:0;">Quý vị đại biểu vui lòng không chuyển tiếp cho người khác</p>
            <p style="font-size:18px;text-align: center; margin:0;">Xin chân thành cảm ơn Quý vị đại biểu.</p>
            <p style="font-size:18px;text-align: center; margin:0;"> Lưu hội nghị vào lịch của Quý vị đại biểu, xin vui lòng <a href="https://vnconference.icu/'.$ics_file_tmp.'" style="background-color: #f4f4f4;">Click vào đây</a></p>
            <p style="font-size:18px;text-align: center; margin:0;">Để chỉnh sửa thông tin, quý đại biểu vui lòng <a href="' . $link_edit_tmp . '" target="_blank" style="color: GREEN;">ấn vào đây</a></p>
        </div>
    </div>
    <div style="margin: 0; padding: 20px; background-color: #f4f4f4; text-align: left;">
        <h2>Kính gửi ' . $row_delegate['delegate_title'] .' '. $row_delegate['delegate_firstname'] . '</h2>
        <p style="font-size:20px;">Xin chân thành cảm ơn ' . $row_delegate['delegate_firstname'] . ' đã đăng ký tham dự Hội thảo khoa học <strong style="color: #ed7c98;">“VẮC-XIN: VŨ KHÍ MỚI TRONG DỰ PHÒNG SỐT XUẤT HUYẾT DENGUE TẠI VIỆT NAM”</strong>.</p>
        <p style="font-size:20px;">' . $info_mail_tmp . '
        <br>
        Thời gian: <strong>' . $time_mail_tmp . '</strong>
        <br>
        <strong>' . $address_mail_tmp . '</strong>
        <br>
        ' . $lable_link_tmp .'<a href="' . $link_tmp. '" target="_blank">(link map)</a></p> 
    </div>
    <div style="margin: 0; padding: 0;">
        <img src="cid:image1_cid" alt="Second Image" style="width: 100%; height: auto; object-fit: cover;" />
    </div>

    <div style="padding: 20px;">
        <p style="font-size:20px;color:black;text-align:center;"> Đây là email tự động, Quý vị vui lòng không cần phản hồi email này.</p>
    </div>

</body>
</html>';

// Nhúng hình ảnh QR
$mail->AddEmbeddedImage($filepath, seoname($row_delegate['delegate_firstname']), $filename);
$mail->AddEmbeddedImage($link_img_tmp, 'image1_cid');
$mail->addAttachment($ics_file, 'appointment.ics', 'base64', 'text/calendar');
if ($mail->send()) {
    echo 'mail sent';
} else {
    echo 'sent error';
}

?>
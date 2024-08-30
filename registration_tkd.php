<?php

use function PHPSTORM_META\type;

include_once 'include/global.php';
include 'qrcode/qrlib.php';
include 'admin/php_image_magician.php';
$conferences_code = $_GET['confCode'];
if($conferences_code == "TKD26SEP"){
    $href = 'registration_mail_ics/appointment_26.ics';
    $download = 'appointment_26.ics';
}
else if($conferences_code == "TKD27SEP"){
    $href = 'registration_mail_ics/appointment_27.ics';
    $download = 'appointment_27.ics';
}
// $conferences_code = 'SANOFI052024';
$conference = $db->rawQueryOne('SELECT * FROM conferences WHERE conferences_code ="' . $conferences_code . '"');
$check_an_overlay = false;
if (isset($_POST['delegate_firstname'])) {
    $delegate_lastchange = date("Y-m-d H:i:s");
    $delegate_firstname = $_POST['delegate_firstname'];
    $delegate_surname = "";
    $delegate_organize = $_POST['delegate_organize'];
    $delegate_phone = $_POST['delegate_phone'];
    $delegate_dob = "";
    $delegate_email = $_POST['delegate_email'];
    $delegate_state = "";
    $delegate_suburb =  "";
    $delegate_title = $_POST['delegate_title'];
    // $delegate_major = $db->where('major_id', $_POST['delegate_major'])->getValue('delegate_major', 'major_name');
    $delegate_major = "";
    $delegate_major_tmp = $_POST['delegate_major'];
    
    if ($delegate_major_tmp == 'other') {

        $delegate_major = $_POST['other_major'];

        $other_major = $_POST['other_major'];
        
        $existing_major = $db->where('major_name', $other_major)->getOne('delegate_major');
        
        if(!$existing_major){
            $sql = "INSERT INTO vcl_delegate_major (major_name) VALUES (?)";
            $result = $db->rawQuery($sql, [$other_major]);
        }
    } else {
        $delegate_major = $db->where('major_id', $_POST['delegate_major'])->getValue('delegate_major', 'major_name');
    }
    $checkDuplicate = $db->rawQueryOne('
    SELECT * FROM `delegate` 
    INNER JOIN `delegate_conf` ON `delegate_conf`.`delegate_id`=`delegate`.`delegate_id` 
    WHERE `delegate_email`="' . $delegate_email . '" AND `conf_id` = "' . $conference['conferences_id'] . '"');
    if (!isset($checkDuplicate['delegate_id'])) {
        # code...
        $delegate_password = $_SESSION['delegate_password'] = rand(100000, 999999);
        $delegate_password = md5($delegate_password);

        //Create code
        //lấy id hội nghị-----
        $conferences_id = intval($conference['conferences_id']);

        //lấy nhóm------------
        $sql = "SELECT * FROM `vcl_delegate_group` WHERE `delegate_group_id`='7' LIMIT 0,1";
        $delegate_group = $db->rawQueryOne($sql);

        $start = intval($delegate_group['delegate_group_start']);
        //--------------------
        $delegate_code = $conference['conferences_code'] . '-' . $delegate_group['delegate_group_code'];
        $sql = "SELECT `delegate_conf_code` FROM `delegate_conf` WHERE `delegate_conf_code` LIKE '$delegate_code%' ORDER BY `delegate_conf_code` DESC LIMIT 1";

        //$sql="SELECT `delegate_code` FROM `delegate` WHERE `delegate_code` LIKE '$delegate_code%' ORDER BY `delegate_id` DESC LIMIT 1";

        $delegate_conf = $db->rawQueryOne($sql);
        //Tách năm
        if (!isset($delegate_conf['delegate_conf_code'])) {
            $next = $start; //echo $delegate_code ; exit();
        } else
            $next = intval(substr($delegate_conf['delegate_conf_code'], -4)) + 1;
        //----------
        //	if($year==$this_year)	
        $delegate_code .= str_pad($next, 4, '0', STR_PAD_LEFT);
        $_SESSION['delegate_code'] = $delegate_code;
        //Create QR
        $PNG_TEMP_DIR = dirname(__FILE__) . DIRECTORY_SEPARATOR . 'admin' . DIRECTORY_SEPARATOR . 'temp' . DIRECTORY_SEPARATOR;

        //html PNG location prefix
        $PNG_WEB_DIR = 'admin/temp/';
        $path = "admin/temp/" . $delegate_code . ".png";
        $path_jpg = "admin/temp/" . $delegate_code . '_' . seoname($delegate_firstname) .'.jpg';
        $_SESSION['path_jpg'] = $path_jpg;
        $filename = $PNG_TEMP_DIR . $delegate_code . '.png';
        $hoten = strtoupper(seoname($delegate_firstname));
        $filename_jpg = $PNG_TEMP_DIR . $delegate_code . '_' . seoname($delegate_firstname) .'.jpg';
        //if (!file_exists($path) || !file_exists($path_jpg)) {
            //echo $filename.'<br>';
            QRcode::png('http://vnconference.icu/?conf=' . $conferences_code . '&code=' . $delegate_code, $filename, 'QR_ECLEVEL_Q', 8, 4);

            //Copy ảnh png ra file jpg
            // Open JPG image
            $magicianObj = new imageLib($filename);
            $font_size = 14;
            $image_size = 180;
            $image_bdSize = 200;
            $radius = 10;
            $font = 'arimo.ttf';
            // while (true) {
            //     $box = @imageTTFBbox($font_size, 0, $font, $hoten);

            //     // ***  Get width of text from dimensions
            //     $textWidth = abs($box[4] - $box[0]);
            //     if ($textWidth > $image_size - 10) {
            //         $font_size -= 1;
            //     } else {
            //         break;
            //     }
            // }
            // $pos_x = ($image_size - $textWidth) / 2;
            // Resize to best fit then crop
            $magicianObj->resizeImage($image_size, $image_size, 'crop');
            // addText([text], [position], [padding], [font_color], [font_size], [angle], [font])
            // $magicianObj->addText($hoten, $pos_x . 'x178', 0, '#ffb3c6', $font_size, 0, $font);
            // $magicianObj->addText($delegate_code, '10x108', 0, '#ff', 7, 0, 'arimo.ttf');
            // Save resized image as a PNG
            $magicianObj->roundCorners($radius, array(39, 156, 172));
            $magicianObj->saveImage($filename_jpg, 100);
            $magicianObjBd = new imageLib($PNG_TEMP_DIR . 'TAKEDA.jpg');
            $magicianObjBd->resizeImage($image_bdSize, $image_bdSize, 'crop');
            $magicianObjBd->roundCorners($radius, array(255, 255, 255));
            $magicianObjBd->addWatermark($filename_jpg, 'm', 0, 100);
            $magicianObjBd->saveImage($filename_jpg, 100);
            $magicianObjBd->saveImage($filename, 100);
        //}
        $delegate = $db->rawQuery(
            "INSERT INTO `delegate` (
            `delegate_code`,
            `delegate_firstname`,
            `delegate_organize`,
            `delegate_phone`,
            `delegate_email`,
            `delegate_password`,
            `delegate_dob`,
            `delegate_state`,
            `delegate_suburb`,
            `delegate_major`,
            `conferences_id`,
            `delegate_title`
        )
        VALUES(
            '$delegate_code' , 
            '".$_POST['delegate_firstname'] ."' ,
            '" .$_POST['delegate_organize'] . "',
            '" .$_POST['delegate_phone'] . "',
            '" .$_POST['delegate_email'] . "',
            '" .$delegate_password . "',
            '" .$_POST['delegate_dob'] . "',
            '" .$delegate_state . "',
            '" .$delegate_suburb . "',
            '" .$delegate_major . "',
            '" .$conferences_id ."',
            '" .$delegate_title ."'
        )"

        );
        $delegate = $db->getInsertId();
        $_SESSION['delegate_id'] = $delegate;
        $delegate_conf = $db->rawQuery(
            "INSERT INTO `delegate_conf` (
            `delegate_id`,
            `delegate_conf_code`,
            `delegate_conf_group`,
            `conf_id`,
            `delegate_conf_checkin`,
            `delegate_checkin`,
            `user_id`,
            `delegate_lastchange`
        )
        VALUES(
            '$delegate','" .
                $delegate_code . "','" .
                $delegate_group['delegate_group_code'] . "'," .
                $conferences_id . ",
            0,
            0,
            0,
            '$delegate_lastchange'
        )"
        );
        $delegate_title_update = $delegate_title;
        $insert_result = 'success';

        //Send-email
        ob_start();
        $sent_result = include 'registration_email_takeda.php';
        // echo '<p> gửi mail'.$sent_result.'</p>';
        
        ob_end_clean();
    } else {
        $insert_result = 'duplicate';
    }
}
?>

<!DOCTYPE html>
<!--[if IE 8]> <html lang="en" class="ie8"> <![endif]-->
<!--[if IE 9]> <html lang="en" class="ie9"> <![endif]-->
<!--[if !IE]><!-->
<html lang="en"> <!--<![endif]-->

<!-- BEGIN HEAD -->

<head>
    <meta charset="UTF-8" />
    <title>VẮC-XIN: VŨ KHÍ MỚI TRONG DỰ PHÒNG SỐT XUẤT HUYẾT DENGUE TẠI VIỆT NAM</title>
    <meta content="width=device-width, initial-scale=1.0" name="viewport" />
    <meta content="Quản lý trung tâm đào tạo" name="description" />
    <meta content="" name="author" />

    <!-- Favicons -->
    <link rel="shortcut icon" href="assets/favicon/favicon.ico" type="image/x-icon" />
    <link rel="apple-touch-icon" href="assets/favicon/apple-touch-icon.png" />
    <link rel="apple-touch-icon" sizes="57x57" href="assets/favicon/apple-touch-icon-57x57.png" />
    <link rel="apple-touch-icon" sizes="72x72" href="assets/favicon/apple-touch-icon-72x72.png" />
    <link rel="apple-touch-icon" sizes="76x76" href="assets/favicon/apple-touch-icon-76x76.png" />
    <link rel="apple-touch-icon" sizes="114x114" href="assets/favicon/apple-touch-icon-114x114.png" />
    <link rel="apple-touch-icon" sizes="120x120" href="assets/favicon/apple-touch-icon-120x120.png" />
    <link rel="apple-touch-icon" sizes="144x144" href="assets/favicon/apple-touch-icon-144x144.png" />
    <link rel="apple-touch-icon" sizes="152x152" href="assets/favicon/apple-touch-icon-152x152.png" />
    <link rel="apple-touch-icon" sizes="180x180" href="assets/favicon/apple-touch-icon-180x180.png" />

    <!-- START GLOBAL CSS -->
    <!-- <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet"
            integrity="sha384-KK94CHFLLe+nY2dmCWGMq91rCGa5gtU4mk92HdvYe+M/SXH301p5ILy+dN9+nJOZ" crossorigin="anonymous">
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/boxicons@latest/css/boxicons.min.css"> -->
    <link rel="stylesheet" href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css">
    <link rel="stylesheet" href="https://booking.vnconference.vn/khotest/incl/libs/bootstrap-5.0.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://booking.vnconference.vn/khotest/incl/libs/bootstrap-icons-1.11.3/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.13.2/themes/base/jquery-ui.min.css" integrity="sha512-ELV+xyi8IhEApPS/pSj66+Jiw+sOT1Mqkzlh8ExXihe4zfqbWkxPRi8wptXIO9g73FSlhmquFlUOuMSoXz5IRw==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.13.2/themes/base/theme.min.css" integrity="sha512-hbs/7O+vqWZS49DulqH1n2lVtu63t3c3MTAn0oYMINS5aT8eIAbJGDXgLt6IxDHcWyzVTgf9XyzZ9iWyVQ7mCQ==" crossorigin="anonymous" referrerpolicy="no-referrer" />

    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.js"></script>
    <link rel="stylesheet" href="assets/pages/global/css/global.css" />
    <link rel="stylesheet" href="assets/pages/global/css/registration.css" />
    <!-- END GLOBAL CSS -->

    <!-- START TEMPLATE GLOBAL CSS -->
    <link rel="stylesheet" href="assets/global/css/components.min.css" />
    <!-- END TEMPLATE GLOBAL CSS -->

    <!-- START LAYOUT CSS -->
    <link rel="stylesheet" href="assets/layouts/layout-top-menu/css/layout.min.css" />
    <link rel="stylesheet" href="assets/pages/login/login-v2/css/login_v2.css" />
    <link rel="stylesheet" href="assets/global/plugins/font-awesome/css/font-awesome.min.css" />
    <?php include 'incl/style.php'; ?>

    <!-- new overlay -->
    <style>
        #overlay {
            position: absolute; /* Làm cho lớp phủ cố định trên màn hình */
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: rgba(240, 177, 221, .5); 
            display: none;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            z-index: 1000; 
            max-height: 100%;
        }    
        .loader {
            position: relative;
            border-style: solid;
            box-sizing: border-box;
            border-width: 40px 60px 30px 60px;
            border-color: #3760C9 #96DDFC #96DDFC #36BBF7;
            animation: envFloating 1s ease-in infinite alternate;
        }

        .loader:after {
            content: "";
            position: absolute;
            right: 62px;
            top: -40px;
            height: 70px;
            width: 50px;
            background-image: linear-gradient(#fff 45px, transparent 0),
                        linear-gradient(#fff 45px, transparent 0),
                        linear-gradient(#fff 45px, transparent 0);
            background-repeat: no-repeat;
            background-size: 30px 4px;
            background-position: 0px 11px , 8px 35px, 0px 60px;
            animation: envDropping 0.75s linear infinite;
        }

        @keyframes envFloating {
            0% {
                transform: translate(-2px, -5px)
            }

            100% {
                transform: translate(0, 5px)
            }
        }

        @keyframes envDropping {
            0% {
                background-position: 100px 11px , 115px 35px, 105px 60px;
                opacity: 1;
            }

            50% {
                background-position: 0px 11px , 20px 35px, 5px 60px;
            }

            60% {
                background-position: -30px 11px , 0px 35px, -10px 60px;
            }

            75%, 100% {
                background-position: -30px 11px , -30px 35px, -30px 60px;
                opacity: 0;
            }
        }

        .btn-success.disabled, .btn-success:disabled {
            color: #fff;
            background: #1a9aad;
            border-color: #1a9aad;
        }
        
        .title {
            font-family: Arial, sans-serif;
            font-size: 24px;
            text-align: center;
            line-height: 1.5;
            font-weight: normal !important; 
            color: #6c6767;
        }

        .title .highlighted {
            color: #ff00db; 
            font-weight: 700;
        }

        .title .bold {
            font-weight: 700; 
            color: #6c6767;
        }

        .checkbox-wrapper-25 input[type="checkbox"] {
            background-image: -webkit-linear-gradient(hsla(0,0%,0%,.1), hsla(0,0%,100%,.1)),
                                -webkit-linear-gradient(left, #f66 50%, #6cf 50%);
            background-size: 100% 100%, 200% 100%;
            background-position: 0 0, 15px 0;
            border-radius: 25px;
            box-shadow: inset 0 1px 4px hsla(0,0%,0%,.5),
                            inset 0 0 10px hsla(0,0%,0%,.5),
                            0 0 0 1px hsla(0,0%,0%,.1),
                            0 -1px 2px 2px hsla(0,0%,0%,.25),
                            0 2px 2px 2px hsla(0,0%,100%,.75);
            cursor: pointer;
            height: 20px;
            padding-right: 25px;
            width: 70px;
            -webkit-appearance: none;
            -webkit-transition: .25s;
        }

        .checkbox-wrapper-25 input[type="checkbox"]:after {
            background-color: #eee;
            background-image: -webkit-linear-gradient(hsla(0,0%,100%,.1), hsla(0,0%,0%,.1));
            border-radius: 25px;
            box-shadow: inset 0 1px 1px 1px hsla(0,0%,100%,1),
                            inset 0 -1px 1px 1px hsla(0,0%,0%,.25),
                            0 1px 3px 1px hsla(0,0%,0%,.5),
                            0 0 2px hsla(0,0%,0%,.25);
            content: '';
            display: block;
            height: 18px;
            width: 45px;
        }

        .checkbox-wrapper-25 input[type="checkbox"]:checked {
            background-position: 0 0, 35px 0;
            padding-left: 25px;
            padding-right: 0;
        }
        .text{
            padding: 0 5px;
        }
    </style>
</head>

<body style="background-image: <?php
                                $config = $db->rawQueryOne('SELECT * FROM `conferences_config` WHERE conf_id = ' . $conference['conferences_id']);
                                echo $config['conf_registration_bg'] != '' ? "url('admin/images/" . $config['conf_registration_bg'] . "')" : '';
                                ?>;">
    
    <!-- Hết -->
    <div class='container-lg' style="max-height: 80%;position: absolute;left: 0;right: 0;top: 15%;">
        
        <div id="registration_container">
            <div class="conference_info">
                <div class="checkbox-wrapper-25" style="display: flex;justify-content: end;padding: 20px;">
                    <span class="text vn">VN</span> <input type="checkbox" id="language"> <span class="text en">EN</span>
                </div>
                <h1 class="title" id="title_conference" data-vn="HỘI THẢO KHOA HỌC<br><span class='highlighted'>VẮC-XIN: VŨ KHÍ MỚI</span><br>TRONG <span class='bold'>DỰ PHÒNG SỐT XUẤT HUYẾT DENGUE</span> TẠI <span class='bold'>VIỆT NAM</span>"
                    data-en="SCIENTIFIC CONFERENCE<br><span class='highlighted'>VACCINE - NEW WEAPON</span><br>IN <span class='bold'>DENGUE FEVER PREVENTION</span> IN <span class='bold'>VIETNAM</span>">
                    HỘI THẢO KHOA HỌC<br>
                    <span class="highlighted">VẮC-XIN: VŨ KHÍ MỚI</span><br>
                    TRONG <span class="bold">DỰ PHÒNG SỐT XUẤT HUYẾT DENGUE</span> TẠI <span class="bold">VIỆT NAM</span>
                </h1>

                <h3 style="font-weight: normal;"><?= $conference['conferences_duration'] ?></h3>
                <h3 style="font-weight: normal;"><?= $conference['conferences_location'] ?></h3>
            </div>

            <div class=" justify-content-center row" id='registration_result'>
                <?php
                if (isset($insert_result)) {
                    echo "
                        <span data-vn='' data-en=''>Chúc mừng quý đại biểu đã đăng ký tham dự hội thảo thành công.  Dưới đây là thông tin <b>Mã QR cá nhân </b> của quý vị dùng điểm danh tại hội thảo, hãy lưu lại Mã QR cá nhân này.<br>
                        Quý Đại biểu vui lòng mang theo <b>THƯ MỜI & Mã QR CÁ NHÂN</b> khi tham dự hội thảo.<br>
                        Vui lòng <b>KHÔNG</b> chia sẻ Mã QR cá nhân và password của Quý vị cho người khác để đảm bảo quyền lợi khi tham dự hội thảo.<br>
                        Xin chân thành cảm ơn.
                        </span>
                        <!--  <button class='btn btn-success' onclick='printDiv()'>In thông tin</button> -->
                        <button class='btn btn-success' onclick='saveImage()' style='background: #1a9aad;' data-vn='Lưu ảnh' data-en=''>Lưu ảnh</button>
                        <button class='btn btn-success' onclick='sentEmail()' style='background: #1a9aad;' data-vn='Gửi lại mail thông tin' data-en=''>Gửi lại mail thông tin</button>
                        <button class='btn btn-success' style='background: #1a9aad;'><a href='".$href."' download='".$download."' style='font-weight: 600;font-size: 14px;color: #fff !important;' data-vn='Lưu lịch sự kiện' data-en=''>Lưu lịch sự kiện</a></button>
                        <p id='mail_result'></p>
                    ";
                }
                ?>
            </div>
            <form method="post" class='delegate_conf justify-content-center row' id="registration_info">
                <?php
                if (isset($insert_result)) {
                ?>
                    <div class="col-12">
                        <p id="delegate_code" style="background: #fb4472;">Mã cá nhân:<br><?= $_SESSION['delegate_code'] ?></p>
                        <p id="delegate_password">
                            Password: <?= $_SESSION['delegate_password'] ?><br>
                            <span>(Dùng để cập nhật thông tin bên dưới nếu có sai lệch)</span>
                        </p>
                        <img id="delegate_qr" src="<?= $_SESSION['path_jpg'] ?>" alt="" style="max-width:200px">
                        <div id="delegate_qr_note">
                            <p>
                                Quý đại biểu vui lòng:
                            </p>
                            <ul>
                                <li>Không chia sẻ mã cá nhân cho người khác.</li>
                                <li>Mang theo THƯ MỜI GIẤY & MÃ QR CÁ NHÂN khi tham dự hội thảo</li>
                            </ul>
                        </div>
                    </div>

                <?php
                }
                ?>
                <!-- Học Hàm Học Vị-->
                <div class='col-md-4 col-sm-12'>
                    <label for='delegate_title' class="form-label" data-vn="Học hàm/Học vị" data-en="Academic title">Học hàm/Học vị</label>
                    <select name="delegate_title" id="delegate_title" class="form-control-sm form-select">
                        <?php 
                        $titles = [
                            "GS. TS. BS.",
                            "GS. TS. DS.",
                            "GS. TS.",
                            "PGS. TS. BS.",
                            "PGS. TS. DS.",
                            "PGS. TS.",
                            "TS. BS.",
                            "Ths. BS.",
                            "Ths. BS. CKII.",
                            "Ths. BS. CKI.",
                            "BS. CKII.",
                            "BS. CKI.",
                            "BS.",
                            "TS. DS.",
                            "Ths. DS.",
                            "Ths. DS. CKII.",
                            "Ths. DS. CKI.",
                            "DS. CKII.",
                            "DS. CKI.",
                            "DS.",
                            "Ths. ĐD.",
                            "Điều dưỡng",
                            "NVYT.",
                            "TS.",
                            "Ths.",
                            "Ông",
                            "Bà"
                        ];

                        foreach ($titles as $title) {
                            $selected = isset($delegate_title_update) && $delegate_title_update == $title ? 'selected' : '';
                            echo "<option value=\"$title\" $selected>$title</option>";
                        }
                        ?>
                        <option value="other">Khác</option>
                    </select>
                    <input type="text" name="other_Academic" id="other_Academic" class="form-control-sm form-control mt-2" placeholder="Nhập học hàm/học vị" style="display: none;">
                </div>
                <!-- Tên -->
                <div class='col-md-4 col-sm-12'>
                    <label for='delegate_firstname' class="form-label" data-vn="Họ và tên" data-en="Academic title">Họ và tên<span style="color:red">*</span></label>
                    <input class="form-control" type="text" name="delegate_firstname" id="delegate_firstname" required value="<?= isset($delegate_firstname)?$delegate_firstname:'' ?>" data-vn='ví dụ Nguyễn Văn Tùng' data-en='' placeholder="ví dụ Nguyễn Văn Tùng" <?= isset($insert_result) && ($insert_result == 'success' || $insert_result == 'duplicate') ? 'readonly' : '' ?>>
                </div>
                <!-- Ngày sinh BỎ-->
                <div class='col-md-4 col-sm-12' style="display:none;">
                    <label for='delegate_dob' class="form-label">Ngày sinh<span style="color:red">*</span></label>
                    <input class="form-control" type="date" name="delegate_dob" id="delegate_dob" min="1920-01-01" value="" <?= isset($insert_result) && ($insert_result == 'success' || $insert_result == 'duplicate') ? 'readonly' : '' ?>>
                </div>
                <!-- Số điện thoại -->
                <div class='col-md-4 col-sm-12'>
                    <label for='delegate_phone' class="form-label" data-vn="Số điện thoại" data-en="Phone ">Số điện thoại<span style="color:red">*</span></label>
                    <input class="form-control" inputmode="numeric" type="number" name="delegate_phone" id="delegate_phone" min="99999999" max="9999999999" required value="<?= $delegate_phone ?>" <?= isset($insert_result) && ($insert_result == 'success' || $insert_result == 'duplicate') ? 'readonly' : '' ?> >
                </div>
                <!-- Email -->
                <div class='col-md-4 col-sm-12'>
                    <label for='delegate_email' class="form-label">Email<span style="color:red">*</span></label>
                    <input class="form-control" type="email" name="delegate_email" id="delegate_email" required value="<?= isset($_POST['delegate_email']) ? $delegate_email : '' ?>" <?= isset($insert_result) && ($insert_result == 'success' || $insert_result == 'duplicate') ? 'readonly' : '' ?> onchange="checkEmail()">
                </div>
                <!-- Cơ quan công tác -->
                <div class='col-md-4 col-sm-12'>
                    <label for='delegate_organize' class="form-label" data-vn="Cơ quan công tác" data-en="Work place">Cơ quan công tác<span style="color:red">*</span></label>
                    <input class="form-control" type="text" name="delegate_organize" id="delegate_organize" required value="<?= isset($delegate_organize)?$delegate_organize:'' ?>" <?= isset($insert_result) && ($insert_result == 'success' || $insert_result == 'duplicate') ? 'readonly' : '' ?>>
                </div>
                <!-- Tỉnh/ thành phố đơn vị công tác BỎ-->
                <div class='col-md-4 col-sm-12' style="display:none;">
                    <label for='delegate_state' class="form-label">Tỉnh/ thành phố đơn vị công tác</label>
                    <select name="delegate_state" id="delegate_state" class="form-control-sm form-select" >
                        <option value="" selected>Chọn tỉnh/ thành phố</option>
                    </select>
                </div>
                <!-- Quận/ Huyện đơn vị công tác BỎ-->
                <div class='col-md-4 col-sm-12' style="display:none;">
                    <label for='delegate_suburb' class="form-label">Quận/ Huyện đơn vị công tác</label>
                    <select name="delegate_suburb" id="delegate_suburb" class="form-control-sm form-select" >
                        <option value="" selected>Chọn quận/ huyện</option>
                    </select>
                </div>
                <!-- Chuyên khoa -->
                <div class="col-md-4 col-sm-12">
                    <label for="delegate_major" class="form-label" data-vn="Chuyên khoa" data-en="Major">
                        Chuyên khoa<span style="color:red">*</span>
                    </label>
                    <select name="delegate_major" id="delegate_major" class="form-control-sm form-select" required <?= isset($insert_result) && ($insert_result == 'success' || $insert_result == 'duplicate') ? 'disabled' : '' ?>>
                        <option value="" selected>
                            Chọn chuyên khoa
                        </option>
                        <?php
                        $majors = $db->orderBy('major_name', 'ASC')->get('delegate_major');
                        foreach ($majors as $major) {
                        ?>
                            <option value="<?= $major['major_id'] ?>" <?= isset($delegate_major) && $major['major_name'] == $delegate_major ? 'selected' : '' ?>>
                                <?= $major['major_name'] ?>
                            </option>
                        <?php
                        }
                        ?>
                        <option value="other">Khác</option>
                    </select>
                    <input type="text" name="other_major" id="other_major" class="form-control-sm form-control mt-2" placeholder="Nhập chuyên khoa khác" style="display: none;">
                </div>
                <?php
                    if (isset($insert_result)) {
                        echo '
                            <div class="col-md-12 col-sm-12">
                            <br>
                                <button id="registration_edit_button" type="button" class="btn btn-success" 
                                onclick="window.open(\'registration_update.php?confCode=' . $conference['conferences_code'] . '\', \'_blank\');">Chỉnh sửa thông tin</button>
                            </div>
                        ';
                    }
                ?>
                <div class="col-12 form-check">
                    <h4 data-vn="PHẦN DÀNH CHO NHÂN VIÊN Y TẾ " data-en="">PHẦN DÀNH CHO NHÂN VIÊN Y TẾ </h4>
                    <input class="form-check-input" type="checkbox" name="" id="registration_checkbox" style="display: inline-block" onchange="agreeRegister(this)" <?= isset($insert_result) && ($insert_result == 'success' || $insert_result == 'duplicate') ? 'checked disabled' : '' ?>>
                    <label for="registration_checkbox" class="form-check-label" data-vn="Tôi hiểu và chấp nhận rằng Takeda sẽ xử lý dữ liệu cá nhân của tôi theo quy định trong Thông báo về quyền riêng tư của Hội nghị " data-en="">Tôi hiểu và chấp nhận rằng Takeda sẽ xử lý dữ liệu cá nhân của tôi theo quy định trong Thông báo về quyền riêng tư của Hội nghị <a href="/docs/TAKEDAT&C&DataPrivacy.html" data-vn="Vui lòng xem chi tiết tại đây" data-en="">Vui lòng xem chi tiết tại đây</a></label>
                    <!-- tại <a href="docs/SANOFIT&C&DataPrivacy.html">“Các Điều Khoản Về Quyền Riêng Tư”</a> -->
                </div>
                <div class='col-md-12 col-sm-12'>
                    <button id="registration_button" type="submit" class="btn btn-success" <?= isset($insert_result) && ($insert_result == 'success' || $insert_result == 'duplicate') ? 'style="display: inline-block;height: 0px;line-height: 0px;overflow: hidden;font-size: 0px;padding: 0px;border: 0px;"' : '' ?> disabled >Đăng ký</button>
                </div>
            </form>
        </div>

        <div class='col-md-12 col-sm-12' style="right: 0;bottom: 0;">
            <p style="text-align:right;padding-bottom: 0;">C-APROM/VN/QDE/0029</p>
        </div>
    </div>
    
    <div id="overlay">
        <div class="loader"></div>
    </div>
</body>
<!-- html2pdf CDN link -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js" integrity="sha512-GsLlZN/3F2ErC5ifS5QtgpiJtWd43JWSuIgh7mbzZ8zBps+dvLusV+eNQATqgA/HdeKFVgA5v3S/cIrLF7QnIg==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
<script>
    
    function printDiv() {
        const element = document.getElementById('registration_info');
        html2pdf().set({
            filename: '<?= isset($delegate_code) ? $delegate_code : $conferences_code ?>' + '.pdf',
        }).from(element).toPdf().save();
    }

    function saveImage() {
        const captureElement = document.querySelector('#registration_container') // Select the element you want to capture. Select the <body> element to capture full page.

        html2canvas(captureElement, {

                allowTaint: true,
                useCORS: true,
                logging: false,
                height: document.querySelector('#registration_container').clientHeight,
            })
            .then(canvas => {
                canvas.style.display = 'none'
                canvas.style.scale = "5"
                document.body.appendChild(canvas)
                return canvas
            })
            .then(canvas => {
                const image = canvas.toDataURL('image/png')
                const a = document.createElement('a')
                a.setAttribute('download', 'my-image.png')
                a.setAttribute('href', image)
                a.click()
                canvas.remove()
            })
    }

    function sentEmail() {
        const load = document.getElementById('overlay');
        load.style.display="flex";
        $.ajax({
            url: 'registration_email_takeda.php',
            type: 'POST',
            data: {
                'conf_code': '<?= $conferences_code ?>',
                'delegate_id': <?= isset($insert_result) && ($insert_result == 'success' || $insert_result == 'duplicate') ? $_SESSION['delegate_id'] : '0' ?>
            },
            success: function(data) {
                if (data) {
                    document.getElementById('mail_result').innerHTML = (data === "mail sent") ? "Thư được gửi thành công" : "Có lỗi khi gửi mail"
                }
                load.style.display="none";
            }
        })
    }

    function checkPhone() {
        $.ajax({
            url: 'registration_email_phone.php',
            type: 'POST',
            data: {
                'checkType': 'phone',
                'confID': <?= $conference['conferences_id'] ?>,
                'checkData': document.getElementById('delegate_phone').value
            },
            success: function(resutl) {
                evt = document.getElementById('delegate_phone')
                if (resutl === 'existed') {
                    evt.setCustomValidity('Số điện thoại đã được đăng ký')
                    evt.reportValidity();
                } else {
                    evt.setCustomValidity('');
                }
            }
        })
    }

    function checkEmail() {
        var email = document.getElementById('delegate_email');
        var pattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

        // Kiểm tra định dạng email trước khi gửi yêu cầu AJAX
        if (!email.value.match(pattern)) {
            email.setCustomValidity('Vui lòng nhập địa chỉ email hợp lệ.');
        } else {
            // Nếu định dạng hợp lệ, xóa thông báo lỗi trước đó
            email.setCustomValidity('');
        }
        // Gọi reportValidity() ở đây để cập nhật trạng thái hợp lệ/ngăn chặn form được submit nếu email không hợp lệ
        email.reportValidity();

        // Chỉ tiếp tục với AJAX nếu email hợp lệ
        if (email.checkValidity()) {
            $.ajax({
                url: 'registration_email_phone.php',
                type: 'POST',
                data: {
                    'checkType': 'email',
                    'confID': <?= $conference['conferences_id'] ?>,
                    'checkData': email.value
                },
                success: function(result) {
                    if (result === 'existed') {
                        email.setCustomValidity('Email đã được đăng ký');
                    } else {
                        email.setCustomValidity('');
                    }
                    // Gọi reportValidity() một lần nữa để cập nhật trạng thái sau khi nhận phản hồi từ server
                    email.reportValidity();
                }
            });
        }
    }

    function agreeRegister(checkboxElem) {
        if (checkboxElem.checked) {
            document.getElementById('registration_button').disabled = false;
        } else {
            document.getElementById('registration_button').disabled = true;
        }
    }

    document.addEventListener('DOMContentLoaded', function () {
        var selectElement = document.getElementById('delegate_major');
        var otherMajorInput = document.getElementById('other_major');

        selectElement.addEventListener('change', function () {
            if (this.value === 'other') {
                otherMajorInput.style.display = 'block';
                otherMajorInput.required = true; // Bắt buộc phải nhập khi chọn "Khác"
            } else {
                otherMajorInput.style.display = 'none';
                otherMajorInput.required = false;
            }
        });

        var selectElement_title = document.getElementById('delegate_title');
        var otherMajorInput_title = document.getElementById('other_Academic');

        selectElement_title.addEventListener('change', function () {
            if (this.value === 'other') {
                otherMajorInput_title.style.display = 'block';
                otherMajorInput_title.required = true; // Bắt buộc phải nhập khi chọn "Khác"
            } else {
                otherMajorInput_title.style.display = 'none';
                otherMajorInput_title.required = false;
            }
        });
    });

    document.getElementById('language').addEventListener('change', function() {
        const isChecked = this.checked;
        const elements = document.querySelectorAll('[data-vn]');

        elements.forEach(element => {
            if (element.tagName.toLowerCase() === 'input' && element.type === 'text') {
            if (isChecked) {
                element.placeholder = element.getAttribute('data-en');
            } else {
                element.placeholder = element.getAttribute('data-vn');
            }
            } else {
                // Thay đổi nội dung cho các thẻ khác
                if (isChecked) {
                    element.innerHTML = element.getAttribute('data-en');
                } else {
                    element.innerHTML = element.getAttribute('data-vn');
                }
            }
        });
    });
</script>

</html>
// ไฟล์: auth/logout.php

<?php
session_start();

// ลบข้อมูลทั้งหมดใน Session
session_unset();

// ทำลาย Session
session_destroy();

// ส่งผู้ใช้กลับไปยังหน้าประวัติ (history.php)
// ⭐️ แก้ไขตรงนี้: จาก header("Location: login.php");
header("Location: history.php"); 
exit();
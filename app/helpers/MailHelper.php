<?php

class MailHelper
{
    /**
     * Gửi email mô phỏng (Dùng cho môi trường Local/Demo)
     * Trong thực tế, bạn sẽ dùng PHPMailer hoặc một service như SendGrid/Brevo.
     */
    public static function send($to, $subject, $title, $description, $link, $buttonText = 'Xác nhận ngay')
    {
        $appUrl = 'http://' . $_SERVER['HTTP_HOST'];
        $fullLink = $appUrl . $link;

        // Template HTML Email chuyên nghiệp
        $html = "
        <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; border: 1px solid #e1e1e1; border-radius: 10px; overflow: hidden;'>
            <div style='background: #1a56db; padding: 25px; text-align: center; color: #ffffff;'>
                <h2 style='margin: 0;'>ShopTech</h2>
            </div>
            <div style='padding: 30px; line-height: 1.6; color: #333;'>
                <h3 style='color: #1a56db;'>$title</h3>
                <p>$description</p>
                <div style='text-align: center; margin: 30px 0;'>
                    <a href='$fullLink' style='background: #1a56db; color: #fff; padding: 12px 25px; text-decoration: none; border-radius: 5px; font-weight: bold;'>$buttonText</a>
                </div>
                <p style='font-size: 0.85rem; color: #777;'>Nếu nút không hoạt động, hãy copy link sau vào trình duyệt:<br>
                <a href='$fullLink'>$fullLink</a></p>
            </div>
            <div style='background: #f9f9f9; padding: 15px; text-align: center; font-size: 0.8rem; color: #999;'>
                &copy; " . date('Y') . " ShopTech - Hệ thống quản lý công nghệ hàng đầu.
            </div>
        </div>
        ";

        /**
         * LOGIC THỰC TẾ:
         * mail($to, $subject, $html, "Content-Type: text/html; charset=UTF-8");
         */

        // LƯU VÀO SESSION ĐỂ MÔ PHỎNG (Hộp thư ảo cho DEV)
        if (!isset($_SESSION['mock_inbox'])) {
            $_SESSION['mock_inbox'] = [];
        }
        
        $_SESSION['mock_inbox'][] = [
            'to' => $to,
            'subject' => $subject,
            'body' => $html,
            'time' => date('H:i:s')
        ];

        return true;
    }
}

<?php

return [
    'failed' => 'Bu kullanıcı bilgileri, bizim verilerle eşleşmiyor.',
    'password' => 'Girilen şifre yanlış.',
    'throttle' => 'Çok fazla oturum açma girişimleri. :seconds saniye içinde tekrar deneyin.',

    'reset_password' => [
        'subject' => 'Parolanızı Sıfırlayın',
        'greeting' => 'Merhaba!',
        'line_1' => 'Bu e-postayı, parola sıfırlama talebinde bulunduğunuz için alıyorsunuz.',
        'action' => 'Parolayı Sıfırla',
        'line_2' => 'Parola sıfırlama bağlantısının süresi :count dakika sonra dolacak.',
        'line_3' => 'Parola sıfırlama talebinde bulunmadıysanız, başka bir işlem yapmanıza gerek yoktur.',
        'salutation' => 'Teşekkürler, Laravel Ekibi',
        'reset_password_send' => 'Parola sıfırlama bağlantısı mailinize gönderildi!',
    ],
    'verify_email' => [
        'subject' => 'E-posta Adresinizi Doğrulayın',
        'greeting' => 'Merhaba!',
        'line_1' => 'E-posta adresinizi doğrulamak için aşağıdaki butona tıklayın.',
        'action' => 'E-postayı Doğrula',
        'line_2' => 'Eğer bu talepte bulunmadıysanız, başka bir işlem yapmanıza gerek yoktur.',
        'salutation' => 'Teşekkürler',
        'fresh_resend' => 'Yeni bir doğrulama bağlantısı mailinize gönderildi.',
        'before_proceeding' => 'Devam etmeden önce, doğrulama bağlantısı için e-postanızı kontrol edin.',
        'you_didnt_receive' => 'E-postayı almadıysanız, başka bir doğrulama e-postası talep edebilirsiniz',
        'resend' => 'başka bir tane talep etmek için buraya tıklayın.',
    ],
    'verification_sent' => 'Doğrulama bağlantısı mailinize gönderildi.',
];

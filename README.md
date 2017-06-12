## English

Most of the code are derived from Ubercart 2CO Payment module. All my code changes are Licenced under GNU/GPL.

### INSTALLATION: 
English installation coming soon.

-------------
## Türkçe
Kodun neredeyse tamamı 2CO ödeme yönteminden alınmış bulunmaktadır. Tüm kod değişiklikleri GNU/GPL altında lisanslıdır. 

### Önemli:
1.Ubercart Ön tanımlı ülke seçmezseniz kredi kartı ekranında iyzico belirmeyecektir.
1.İyzico için iyzipay-php eklentisini kurmalısınız. https://github.com/iyzico/iyzipay-php adresinden indirilen çalışıcaktır. Eğer Laravel v.s. diğer eklentileri libraries klasörünün altına kurarsanız çalışmama ihtimali var.

### Kurulum:

Drupal 8 Kurulumunuzun kök dizini / olmak üzere
1. iyzipay php kütüphanesini /libraries klasörü altına yapıştırın ve yeniden adlandırın sonuçta /libraries/iyzipay olsun.
1. uc_iyzipay modulünü /modules/ubercart/payment klasörünün altına yapıştırın.
1. Drupal Kurulumunuzun /admin/modules sayfasını ziyaret edip;
1. Ubercart için gerekli modülleri aktif hale getirin.
1. uc_iyzipay modülünü aktif hale getirin.
1. Ubercart için iyzico ödeme yöntemi ekleyin
1. [Iyzico için API bilgileri v.b. girin.](https://github.com/EnginYilmaz/ubercart-iyzico/blob/iyzipay/images/kurulum-resmi.png)
1. Kullanıma hazır.



-------------
Credidentals:
Special thanks to author and maintainers of the 2CO payment method. (Their names will be added soon)

2017 Engin YILMAZ (copy left). Ücretli destek için engin@webstudio.web.tr GSM: 05324029677

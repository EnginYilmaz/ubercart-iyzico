Most of the code are derived from Ubercart 2CO Payment module. All my code changes are Licenced under GNU/GPL. Engin YILMAZ

INSTALLATION: Put the uc_iyzipay module under /modules/ubercart/payment/ directory

At the moment the only way to make this code work is in a hackish way.

USAGE: Set your class with your existing API credidentials in necessary files

TODO:

Automation between configuration screen and API Credidentials
Seperating the Iyzipay library from the code
a- Full Integration of the payment page b- Full Integration of the checkout page

-------------
Türkçe

Drupal 8 Kurulumunuzun kök dizini / olmak üzere
1-) iyzipay kütüphanesini /libraries klasörü altına yapıştırın
2-) uc_iyzipay modulünü /modules/ubercart/payment klasörünün altına yapıştırın.
3-) Drupal Kurulumunuzun /admin/modules sayfasını ziyaret edip;
		a) Ubercart için gerekli modülleri aktif hale getirin.
    b) uc_iyzipay modülünü aktif hale getirin. 

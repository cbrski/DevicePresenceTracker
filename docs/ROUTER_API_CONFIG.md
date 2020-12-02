##### PL
### Konfiguracja API routera


##### Wymagane klucze
W pliku **.env** należy umieścić takie klucze jak:

```env
OPENWRT_API_LOGIN=
OPENWRT_API_PASSWORD=
OPENWRT_API_HOST=
OPENWRT_API_URL_AUTH=
OPENWRT_API_URL_NEIGHBOURS=
OPENWRT_API_SESSION_TIMEOUT=3500
OPENWRT_API_FILE_TIMESTAMP_HELPER=RouterApiTimestamp
```
gdzie:
- *LOGIN* to login konta RCP
- *PASSWORD* to hasło konta RCP
- *HOST* to adres routera w sieci
- *URL_AUTH* to ścieżka dostępu do uwierzytelnienia RCP
- *URL_NEIGHBOURS* to ścieżka dostępu do pobrania danych o stanach urządzeń
- *SESSION_TIMEOUT* to ilość sekund jak długo token uwierzytelnienia poprzez RCP jest ważny (zazwyczaj 1 godzina)
<br>*(w listingu pozostawiono wartość proponowaną)*
- *FILE_TIMESTAMP_HELPER* to nazwa pliku pomocniczego który przetrzymuje ostatni czas uwierzytelnienia poprzez RCP
<br>*(w listingu pozostawiono wartość proponowaną)*

##### Definiowanie własnych nazw urządzeń

Dodatkowo istnieje możliwość skonfigurowania nazw urządzeń.
<br>W pliku **.env** należy dodać takie klucze jak:
```env
OPENWRT_MAP_DEVICE_1=<nazwa>..<adres_mac>
```
gdzie "*\<nazwa\>*" to nazwa główna, zaś "*\<adres_mac\>*" to adres mac urządzenia

Należy pamiętać że nie można stosować białych znaków.
Nazwa urządzenia od adresu mac jest oddzielona łańcuchem znaków "__..__" (dwie kropki).

Można zdefiniować wiele nazw urządzeń. Należy pamiętać aby każdy następny klucz miał dołączony następny numer:
```env
OPENWRT_MAP_DEVICE_1=<nazwa>..<adres_mac>
OPENWRT_MAP_DEVICE_2=<nazwa>..<adres_mac>
OPENWRT_MAP_DEVICE_3=<nazwa>..<adres_mac>
OPENWRT_MAP_DEVICE_4=<nazwa>..<adres_mac>
...
```

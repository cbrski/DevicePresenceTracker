### Device Presence Tracker

#### Cel:
 1. Jakie urządzenia aktualnie (od jak dawna) korzystają z mojej sieci ?
 1. Jeżeli urządzenie już nie korzysta z mojej sieci to kiedy ostatnio korzystało ?
 1. Jakie dane są przypisane do urządzenia (interfejs, adres ip, adres mac, nazwa hosta) ?

#### Wymagania:
 - router z OpenWrt
 - mały serwer<br>_(a'la Raspberry Pi)_

#### Realizacja:
Użytkownik otrzymuje przejrzysty interefejs _(1, 2)_,
z dostępem do szczegółowych informacji _(3)_ po kliknięciu / tapnięciu w kartę z danym urządzeniem. 

:point_right: [Statyczne demo dostępne tutaj](https://cbrski.github.io/DevicePresenceTracker) :point_left:

##### Legenda:

 - Urządzenie online:
<p align="left"><img src="https://raw.githubusercontent.com/cbrski/DevicePresenceTracker/c71d253007a995b5bea4b36a67dd3555b9f2d6d8/docs/graphics/card_online.svg" width="300px"></p>

 - Urządzenie prawdopodobnie online (dotyczy urządzeń połączonych bezprzewodowo):
<p align="left"><img src="https://raw.githubusercontent.com/cbrski/DevicePresenceTracker/c71d253007a995b5bea4b36a67dd3555b9f2d6d8/docs/graphics/card_online_maybe.svg" width="300px"></p>

- Urządzenie offline:
<p align="left"><img src="https://raw.githubusercontent.com/cbrski/DevicePresenceTracker/c71d253007a995b5bea4b36a67dd3555b9f2d6d8/docs/graphics/card_offline.svg" width="300px"></p>

- Dane urządzenia:
<p align="left"><img src="https://raw.githubusercontent.com/cbrski/DevicePresenceTracker/c71d253007a995b5bea4b36a67dd3555b9f2d6d8/docs/graphics/card_details.svg" width="300px"></p>

:point_right: [Statyczne demo dostępne tutaj](https://cbrski.github.io/DevicePresenceTracker) :point_left:

Stack technologiczny:
<br>PHP 7, Laravel 8, Bootstrap 4

[Jak to działa?](docs/HOW_IT_WORKS.md)

[Konfiguracja API dla połączenia z routerem](docs/ROUTER_API_CONFIG.md)

### Router (OpenWrt)

:information_source: [Struktura plików *.bash* na routerze jest dostępna w gałęzi 'router'](https://github.com/cbrski/DevicePresenceTracker/tree/router)

Komunikacja z routerem poprzez JSON-RPC.

Dane pozyskujemy głównie dzięki komendzie:
```bash
ip neigh
```
wraz z asystą komend:
```bash
cat /tmp/dhcp.leases*
iwinfo wlan0 assoclist
```
[Dostępne stany urządzeń](https://www.man7.org/linux/man-pages/man8/ip-neighbour.8.html) to wartości "STATE" komendy "ip neigh".

Główna logika skryptu 
[dostępna tutaj (*main.sh*)](https://github.com/cbrski/DevicePresenceTracker/blob/router/var/DevicePresenceTracker/main.sh)

### Serwer

:information_source: [Konfiguracja API dla połączenia z routerem](ROUTER_API_CONFIG.md)

Obecnie jest wykorzystywany cron aby odpytywać router co 1 minutę o stany urządzeń.

Udostępniona komenda do odpytywania to:
```bash
php artisan command:pullNeighboursFromRouter
```

Klasa odpowiedzialna za przetworzenie danych z routera to:
```php
\App\StorageBroker\DeviceStateInput
```
przekazuje ona listę aktualnych urządzeń z routera `\App\StorageBroker\Helpers\NeighboursRepository` dla: 
```php
\App\StorageBroker\Helpers\VisibleDevicesSynchronizator
```
następnie tworzona jest kolekcja `\Illuminate\Support\Collection` z abstrakcyjnymi obiektami `\App\StorageBroker\Helpers\VisibleDeviceSynchronizator\VisibleDeviceKeeper` (obiekty te przetrzymują w sobie powiązane ze sobą 3 modele `Eloquent ORM` dla każdego "widocznego urządzenia", tzn takiego, które jest widoczne jako [karta](https://cbrski.github.io/DevicePresenceTracker/) pod ścieżką `\tracker`)
wówczas obie kolekcje danych są przekazywane do łańcucha odpowiedzialności, gdzie:
1. `\App\StorageBroker\Helpers\VisibleDeviceSynchronizator\MatchMaker` odpowiada za:
    mapowanie urządzenia z routera do "widocznego urządzenia"
1. `\App\StorageBroker\Helpers\VisibleDeviceSynchronizator\MatchedUpdater` odpowiada za:
    aktualizację "widocznego urządzenia" zgodnie z jego stanem na routerze
1. `\App\StorageBroker\Helpers\VisibleDeviceSynchronizator\NotMatchedUpdater` odpowiada za:
    zmianę stanu "widocznego urządzenia" na `_offline` (gdyż nie znajduje się już ono w tablicy ARP na routerze)
1. `\App\StorageBroker\Helpers\VisibleDeviceSynchronizator\Creator` odpowiada za:
    utworzenie nowych "widocznych urządzeń" dla urządzeń z routera które nie zostały zmapowane

####Router (OpenWrt)

Dane pozyskujemy głównie dzięki komendzie:
```bash
ip neigh
```
wraz z asystą komend:
```bash
cat /tmp/dhcp.leases*
iwinfo wlan0 assoclist
```

Główny skrypt zwracający dane w formacie JSON znajduje się w gałęzi "router" tego repozytorium pod ścieżką:
```bash
/var/DevicePresenceTracker/main.sh
```

Dane z routera można pobrać tylko po uwierzytelnieniu poprzez protokół RPC.

Struktura plików na routerze dostępna jest w gałęzi 'router'.

####Serwer

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
Jej zadanie to zapisanie do bazy danych aktualnych stanów urządzeń.

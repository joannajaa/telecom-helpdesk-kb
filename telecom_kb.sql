-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Wrz 04, 2026 at 11:55 PM
-- Wersja serwera: 10.4.32-MariaDB
-- Wersja PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `telecom_kb`
--

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `articles`
--

CREATE TABLE `articles` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `content` text NOT NULL,
  `image` varchar(255) DEFAULT NULL,
  `category_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `is_pinned` tinyint(1) NOT NULL DEFAULT 0,
  `is_archived` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `articles`
--

INSERT INTO `articles` (`id`, `title`, `content`, `image`, `category_id`, `user_id`, `is_pinned`, `is_archived`, `created_at`) VALUES
(2, 'Czerwona dioda LOS na terminalu ONT', 'Klient zgłasza całkowity brak dostępu do internetu. Na terminalu optycznym (ONT/GPON) dioda oznaczona jako LOS (Loss of Signal) świeci lub miga na czerwono, a dioda PON jest zgaszona. Oznacza to brak sygnału optycznego docierającego z centrali (OLT).\r\n\r\nKroki diagnostyczne dla konsultanta:\r\n1. Zweryfikuj w systemie bilingowo-technicznym, czy w rejonie klienta nie występuje awaria masowa (prace modernizacyjne, uszkodzenie magistrali światłowodowej).\r\n\r\n2. Sprawdź status konta abonenta (brak blokady za płatności).\r\n\r\n3. Poproś klienta o lokalizację zielonego złącza światłowodowego (patchcord SC/APC) z tyłu lub u dołu urządzenia ONT.\r\n\r\nProcedura rozwiązania problemu:\r\n\r\n1. Weryfikacja okablowania klienta:\r\nUpewnij się, że żółty przewód światłowodowy nie jest mocno zagięty (promień gięcia nie może być mniejszy niż 3 cm), przycięty drzwiami lub uszkodzony mechanicznie.\r\n\r\n2. Poinstruuj klienta, aby ostrożnie wyjął wtyk optyczny, przedmuchał gniazdo i wpiął wtyczkę ponownie, aż usłyszy charakterystyczne kliknięcie zatrzasku.\r\n\r\n3. Restart urządzenia:\r\nWyłącz terminal zasilaczem z gniazdka na 30 sekund i włącz ponownie. Odczekaj 2 minuty na próbę ponownej synchronizacji.\r\n\r\n4. Działanie końcowe:\r\nJeśli po 2 minutach dioda LOS nadal świeci na czerwono, problem leży w linii napowietrznej/ziemnej lub w szafie dystrybucyjnej.\r\n\r\nDyspozycja: Załóż zgłoszenie techniczne do serwisu terenowego z kodem: Uszkodzenie traktu optycznego/brak mocy optycznej.', 'img_6a9866f945a239.76827209.jpg', 1, 1, 0, 0, '2026-09-02 18:12:09'),
(3, 'Diagnostyka błędu braku uprawnień i problemów z odtwarzaniem kanałów w usłudze IPTV', 'Klient zgłasza brak możliwości oglądania kanałów telewizyjnych. Na ekranie telewizora wyświetla się biały obraz lub komunikat systemowy: „Błąd autoryzacji / Brak uprawnień do danego pakietu” albo „Błąd strumienia (kod: E-401 / E-502)”. Internet na innych urządzeniach może działać prawidłowo.\r\n\r\nKroki diagnostyczne dla konsultanta:\r\n1. Sprawdź w systemie bilingowym stan pakietów telewizyjnych abonenta oraz status płatności (brak blokady windykacyjnej na usługę TV).\r\n\r\n2. Zweryfikuj w panelu zarządzania dekoderami, czy urządzenie klienta ma status Online i czy przypisany jest poprawny profil subskrypcji (poprawny VLAN).\r\n\r\n3. Sprawdź, czy dekoder łączy się z routerem przewodowo (kabel Ethernet RJ45), czy bezprzewodowo (Wi-Fi).\r\n\r\nProcedura rozwiązania problemu:\r\n1. Restart i weryfikacja połączenia sieciowego:\r\n-Poinstruuj klienta, aby sprawdził połączenie kabla Ethernet między routerem a dekoderem (dioda portu LAN na routerze powinna mrugać). Jeśli dekoder jest włączony, a połączenie kablowe nie działa, kabel ethernetowy może być uszkodzony.\r\n-Zalecana kolejność restartu: najpierw zrestartuj ONT, następnie router i poczekaj na pełną synchronizację (diody internetu/PON stałe), następnie odłącz zasilanie dekodera na 20 sekund.\r\n\r\n2. Wymuszenie odświeżenia uprawnień (Push Provisioning):\r\n-Z poziomu systemu konsultanta wyślij ponowną autoryzację pakietu\r\n\r\n3. Przywrócenie ustawień fabrycznych dekodera (opcjonalnie)\r\nhttps://www.youtube.com/watch?v=M3ut7ZiHG7M\r\n\r\n4. Działanie końcowe:\r\nJeśli dekoder nie pobiera adresu IP z routera lub zgłasza błąd sprzętowy pamięci/karty: załóż zlecenie wymiany urządzenia w punkcie obsługi (BOA) lub wyślij zgłoszenie do serwisu.', 'img_6a986b6eb59a34.53301701.jpg', 4, 1, 0, 0, '2026-09-02 18:31:10'),
(4, 'Optymalizacja zasięgu Wi-Fi: diagnostyka zakłóceń i konfiguracja pasm 2.4 GHz oraz 5 GHz', 'Klient zgłasza częste zrywanie połączenia bezprzewodowego, niski transfer (znacznie niższy niż przepustowość łącza na umowie) lub gwałtowny spadek prędkości w innych pomieszczeniach. Często dotyczy to mieszkań w blokach lub domów o grubszych ścianach.\r\n\r\nKluczowe różnice między pasmami:\r\n🌐 Pasmo 2.4 GHz: Lepsza propagacja fal (większy zasięg, lepsze przenikanie przez ściany), ale mniejsza maksymalna przepustowość i duże ryzyko zakłóceń od sąsiednich sieci, mikrofalówek czy urządzeń Bluetooth.\r\n🚀 Pasmo 5 GHz: Bardzo wysoka przepustowość i znacznie mniejsze zatłoczenie kanałów, lecz wyraźnie mniejszy zasięg fizyczny i słabe przenikanie przez przeszkody budowlane.\r\n\r\nKroki diagnostyczne dla konsultanta:\r\n1. Sprawdź, czy problem występuje na wszystkich urządzeniach (smartfon, laptop), czy tylko na jednym (co wskazuje na problem z kartą sieciową klienta).\r\n2. Zweryfikuj prędkość po kablu Ethernet (Speedtest na PC), aby wykluczyć usterkę na linii dosyłowej lub uszkodzenie portu WAN \r\n3. Zaloguj się zdalnie do routera lub ONT i sprawdź aktualnie nadawane pasma, zajętość kanałów oraz liczbę podłączonych urządzeń.\r\n\r\nProcedura optymalizacji i konfiguracji:\r\n1. Fizyczna lokalizacja routera:\r\n-Poinstruuj klienta, aby router stał na otwartej przestrzeni, na wysokości ok. 1–1.5 m (nie w szafce, za telewizorem ani na podłodze).\r\n-Upewnij się, że urządzenie nie znajduje się bezpośrednio obok innych źródeł fal radiowych lub dużych metalowych przeszkód.\r\n2.Rozdzielenie pasm (oddzielne SSID):\r\n-W ustawieniach routera wyłącz wspólną nazwę sieci (funkcję Smart Connect/Band Steering), jeśli sprawia ona problem starszym urządzeniom.\r\n3. Skonfiguruj dwie odrębne sieci, np.:\r\nNazwaSieci_2.4GHz (dla urządzeń oddalonych, smart home, drukarek)\r\nNazwaSieci_5GHz (dla laptopów, konsol i telefonów używanych bliżej routera)\r\n4. Wybór optymalnego kanału:\r\n-Dla pasma 2.4 GHz ustaw ręcznie jeden z nienakładających się kanałów: 1, 6 lub 11 (o szerokości pasma 20 MHz, co redukuje zakłócenia).\r\n-Dla pasma 5 GHz wybierz kanał z zakresu 36–48 o szerokości 80 MHz w celu uzyskania maksymalnej przepustowości.\r\n\r\nZalecenia końcowe:\r\nJeśli metraż lokalu przekracza efektywny zasięg jednego routera (np. dom piętrowy), zaproponuj rozbudowę instalacji o punkty dostępowe (Access Point) lub system Mesh spięty szkieletem kablowym Ethernet.', 'img_6a986cf6647b75.06353853.jpg', 2, 3, 0, 0, '2026-09-02 18:37:42'),
(5, 'Brak synchronizacji ONT – migająca dioda PON', 'Opis problemu\r\nKlient zgłasza brak dostępu do Internetu. Na urządzeniu ONT dioda PON miga, nie świeci albo urządzenie nie przechodzi w stan synchronizacji. Dioda LOS może dodatkowo świecić lub migać na czerwono.\r\n\r\nMożliwe przyczyny\r\npoluzowany lub odłączony przewód światłowodowy,\r\nzbyt mocno zagięty albo uszkodzony patchcord,\r\nzabrudzone złącze optyczne,\r\nchwilowa awaria lub prace serwisowe w sieci,\r\nproblem z zasilaniem urządzenia ONT,\r\nbrak prawidłowej rejestracji ONT w systemie operatora,\r\nuszkodzenie terminala optycznego albo infrastruktury światłowodowej.\r\nKroki diagnostyczne\r\nSprawdź w systemie, czy w lokalizacji klienta występuje awaria masowa lub zaplanowane prace techniczne.\r\nPoproś klienta o sprawdzenie zasilacza oraz przewodu zasilającego ONT.\r\nSprawdź, czy przewód światłowodowy jest prawidłowo podłączony do gniazda optycznego i urządzenia ONT.\r\nUpewnij się, że przewód nie jest mocno zagięty, przyciśnięty meblem ani uszkodzony mechanicznie.\r\nNie dotykaj końcówki złącza optycznego i nie patrz bezpośrednio w gniazdo światłowodowe.\r\nWykonaj restart urządzenia: odłącz zasilanie ONT na około 30 sekund, następnie podłącz je ponownie.\r\nOdczekaj do 5 minut na ponowną synchronizację urządzenia.\r\n\r\nSprawdź stan diod:\r\nPON świeci na stałe - urządzenie jest prawidłowo zsynchronizowane,\r\nPON miga - trwa próba synchronizacji,\r\nLOS świeci lub miga na czerwono - urządzenie nie otrzymuje prawidłowego sygnału optycznego.\r\nDziałanie końcowe\r\nJeśli po restarcie dioda PON nadal miga, a usługa nie działa, należy sprawdzić status ONT w systemie operatora. Jeżeli urządzenie nie jest zarejestrowane lub nie ma sygnału optycznego, utwórz zgłoszenie do działu technicznego albo serwisu terenowego.\r\n\r\nW zgłoszeniu należy podać:\r\n\r\nnumer klienta,\r\nadres instalacji,\r\nnumer seryjny ONT,\r\nstan diod PON i LOS,\r\ngodzinę wystąpienia problemu,\r\ninformację o wykonanym restarcie urządzenia.\r\n\r\nNie zaleca się samodzielnego czyszczenia ani rozłączania złączy światłowodowych przez klienta.', '0c79478bd4d4263e33a0476377f38ddb.png', 1, 4, 0, 0, '2026-09-04 20:54:42'),
(6, 'Router nie przydziela adresu IP przez DHCP', 'Klient zgłasza, że urządzenie - komputer, telefon, telewizor lub dekoder - łączy się z routerem, ale nie ma dostępu do Internetu. W ustawieniach sieci widoczny jest adres zaczynający się od 169.254.x.x albo urządzenie nie otrzymuje żadnego adresu IP.\r\n\r\nMożliwe przyczyny\r\n-wyłączony serwer DHCP w routerze,\r\n-zawieszony router lub usługa DHCP,\r\n-wyczerpana pula dostępnych adresów IP,\r\n-problem z kablem Ethernet albo portem LAN,\r\n-konflikt adresów IP,\r\n-błędna konfiguracja urządzenia klienta,\r\n-nieprawidłowa konfiguracja trybu pracy routera,\r\n-uszkodzenie lub przeciążenie urządzenia.\r\n\r\nKroki diagnostyczne\r\n-Sprawdź, czy problem dotyczy jednego urządzenia, czy wszystkich urządzeń w sieci.\r\n-Sprawdź połączenie z routerem oraz stan diod przy porcie LAN.\r\n-Jeśli urządzenie jest połączone przewodowo, podłącz je do innego portu LAN lub użyj innego kabla.\r\n-Jeśli korzysta z Wi‑Fi, rozłącz sieć i połącz się z nią ponownie.\r\n-Uruchom ponownie router, odłączając go od zasilania na około 30 sekund.\r\n-Na komputerze z systemem Windows otwórz wiersz polecenia i wykonaj:\r\n\r\n\r\n\"ipconfig /release\r\nipconfig /renew\r\nipconfig /flushdns\"\r\n\r\n\r\n-Sprawdź, czy urządzenie otrzymało poprawny adres IP, bramę domyślną i serwer DNS.\r\n-Zaloguj się do panelu routera i sprawdź ustawienia DHCP:\r\n    -serwer DHCP powinien być włączony,\r\n    -zakres adresów powinien obejmować wolne adresy,\r\n    -pula nie powinna być wyczerpana.\r\n-Sprawdź listę aktywnych klientów DHCP i usuń nieaktualne wpisy, jeśli router na to pozwala.\r\n-Jeżeli problem dotyczy wszystkich urządzeń, sprawdź połączenie routera z modemem lub terminalem ONT.\r\n\r\nDziałanie końcowe\r\nJeśli po restarcie router nadal nie przydziela adresów IP, wykonaj kopię konfiguracji i przywróć ustawienia DHCP do prawidłowych wartości. Nie wykonuj resetu fabrycznego bez zgody klienta, ponieważ może to usunąć dane dostępowe do Internetu.\r\n\r\nJeżeli problem nadal występuje:\r\n-sprawdź status usługi w systemie operatora,\r\n-zweryfikuj działanie modemu lub ONT,\r\n-przetestuj połączenie innym urządzeniem,\r\n-utwórz zgłoszenie techniczne,\r\n-w razie potrzeby skieruj urządzenie do wymiany.\r\n\r\nOczekiwany rezultat\r\nUrządzenie powinno otrzymać adres z prywatnego zakresu, np.:\r\n192.168.1.x,\r\n192.168.0.x,\r\n10.x.x.x.\r\nAdres 169.254.x.x oznacza zwykle, że urządzenie nie otrzymało adresu z serwera DHCP.', '3726eb30c192eb37b595f0c838c4e877.jpg', 2, 4, 0, 0, '2026-09-04 20:59:34'),
(7, 'Zacinanie obrazu  IPTV', 'Klient zgłasza, że obraz na kanałach IPTV zatrzymuje się i pojawiają się przerwy w dźwięku. Może pojawić się tzw. pikseloza. Problem może występować stale albo tylko w określonych godzinach.\r\n\r\nMożliwe przyczyny\r\n-niestabilne połączenie z Internetem,\r\n-zbyt niska prędkość połączenia,\r\n-duże obciążenie sieci domowej,\r\n-połączenie dekodera przez zakłócane Wi‑Fi,\r\n-uszkodzony lub zbyt długi kabel Ethernet,\r\n-przeciążenie routera,\r\n-problem po stronie platformy IPTV,\r\n-chwilowa awaria lub prace techniczne.\r\n\r\nKroki diagnostyczne\r\n-Sprawdź, czy problem występuje na wszystkich kanałach, czy tylko na jednym.\r\n-Sprawdź, czy Internet działa poprawnie na innych urządzeniach.\r\n-Wykonaj test prędkości połączenia na komputerze podłączonym przewodem Ethernet.\r\n-Sprawdź, czy dekoder jest podłączony kablem Ethernet. Jeśli korzysta z Wi‑Fi, w miarę możliwości użyj połączenia przewodowego.\r\n-Sprawdź przewód Ethernet i podłącz go do innego portu LAN routera.\r\n-Zamknij pobieranie plików, streaming i inne działania obciążające sieć.\r\n-Uruchom ponownie w odpowiedniej kolejności:\r\n    -ONT lub modem,\r\n    -router,\r\n    -dekoder IPTV.\r\n    -Odczekaj kilka minut na pełną synchronizację urządzeń.\r\n-Sprawdź aktualizacje oprogramowania dekodera.\r\n-Jeżeli problem dotyczy wyłącznie jednego kanału, sprawdź komunikaty operatora dotyczące tego kanału.\r\n\r\nDziałanie końcowe\r\nJeśli buforowanie nadal występuje:\r\n-wykonaj test połączenia przewodowego,\r\n-sprawdź poziom obciążenia routera,\r\n-zweryfikuj, czy w lokalizacji występuje awaria,\r\n-sprawdź usługę na innym dekoderze lub telewizorze, jeśli jest dostępny,\r\n-utwórz zgłoszenie techniczne. W zgłoszeniu podaj:\r\n    -numer klienta,\r\n    -nazwę kanału, na którym występuje problem,\r\n    -datę i godzinę występowania zakłóceń,\r\n    -sposób połączenia dekodera — Ethernet czy Wi‑Fi,\r\n    -wynik testu prędkości,\r\n    -informację o wykonanych restartach.\r\n\r\nOczekiwany rezultat\r\n-Po prawidłowym przywróceniu połączenia obraz powinien być płynny, bez zatrzymywania, pikselizacji i przerw w dźwięku.', '307e227791578cc0cea579ec4913553a.jpg', 4, 4, 0, 0, '2026-09-04 21:05:05'),
(8, 'Brak kanałów po automatycznym wyszukiwaniu', 'Opis problemu\r\nKlient zgłasza, że telewizor lub dekoder nie znalazł żadnych kanałów po wykonaniu automatycznego wyszukiwania. Może pojawić się komunikat „Brak sygnału” albo lista kanałów pozostaje pusta.\r\n\r\nMożliwe przyczyny\r\n-odłączony lub uszkodzony przewód antenowy,\r\n-wybrany nieprawidłowy typ sygnału,\r\n-zła lokalizacja lub ustawienia kraju,\r\n-zbyt słaby sygnał antenowy,\r\n-nieprawidłowo ustawiona antena,\r\n-użycie niewłaściwego gniazda w telewizorze,\r\n-rozpoczęcie wyszukiwania bez podłączonego kabla,\r\n-chwilowa przerwa w nadawaniu albo prace techniczne,\r\n-nieaktualne oprogramowanie telewizora lub dekodera.\r\n\r\nKroki diagnostyczne\r\n-Sprawdź, czy przewód antenowy jest prawidłowo podłączony do telewizora lub dekodera.\r\n-Sprawdź drugi koniec przewodu - powinien być podłączony do gniazda antenowego lub instalacji antenowej.\r\n-Jeżeli jest używany rozdzielacz antenowy, sprawdź jego podłączenie i zasilanie.\r\n-Wejdź w ustawienia wyszukiwania kanałów.\r\nWybierz właściwy typ sygnału:\r\n    -DVB-T/Antena - dla telewizji naziemnej,\r\n    -DVB-C/Kabel - dla telewizji kablowej.\r\n-Ustaw prawidłowy kraj i rozpocznij ponowne wyszukiwanie automatyczne.\r\n-Jeśli dostępna jest opcja zasilania anteny, włącz ją tylko wtedy, gdy instalacja korzysta ze wzmacniacza wymagającego zasilania.\r\n-Sprawdź poziom i jakość sygnału w menu diagnostycznym urządzenia.\r\n-Jeśli to możliwe, wykonaj test na innym kablu antenowym lub innym telewizorze.\r\n-Sprawdź, czy nie ma zgłoszonej awarii albo zmiany parametrów nadawania.\r\n\r\nDziałanie końcowe\r\nJeśli wyszukiwanie nadal nie znajduje kanałów:\r\n-sprawdź instalację antenową w lokalu,\r\n-zweryfikuj stan gniazda antenowego,\r\n-sprawdź antenę, wzmacniacz i rozdzielacz,\r\n-w przypadku DVB-C potwierdź u operatora aktualne parametry wyszukiwania,\r\n-utwórz zgłoszenie techniczne, jeśli problem dotyczy całej instalacji.\r\n-Nie należy samodzielnie wchodzić na dach ani wykonywać prac przy zewnętrznej antenie bez odpowiednich uprawnień i zabezpieczeń.\r\n\r\nOczekiwany rezultat\r\nPo prawidłowym wyborze typu sygnału i poprawnym podłączeniu instalacji telewizor powinien znaleźć dostępne kanały oraz wyświetlać je bez komunikatu „Brak sygnału”.', '50402e983d4a95eab6ba14af34df51db.jpg', 5, 4, 0, 0, '2026-09-04 21:08:53'),
(9, 'Procedura komunikowania klientom planowanych prac serwisowych', 'Cel procedury\r\nCelem procedury jest przekazanie klientowi jasnej i rzetelnej informacji o planowanych pracach technicznych, możliwych przerwach w działaniu usług oraz przewidywanym czasie ich zakończenia.\r\n\r\nPrzed kontaktem z klientem\r\n-Sprawdź w systemie zakres prac i obszar, którego dotyczą.\r\n-Zweryfikuj planowaną datę oraz godziny rozpoczęcia i zakończenia.\r\n-Sprawdź, których usług może dotyczyć przerwa:\r\n    -Internet,\r\n    -telewizja,\r\n    -telefonia,\r\n    -usługi dodatkowe.\r\n-Ustal, czy prace obejmują adres klienta.\r\n-Sprawdź, czy dostępny jest oficjalny komunikat operatora.\r\n-Sposób przekazania informacji\r\n\r\nPodczas rozmowy należy:\r\n-Poinformować, że w danej lokalizacji zaplanowano prace serwisowe.\r\n-Podać dokładny termin rozpoczęcia i przewidywanego zakończenia.\r\n-Wyjaśnić, które usługi mogą być czasowo niedostępne.\r\n-Poinformować, że podane godziny są orientacyjne, jeśli operator nie gwarantuje dokładnego czasu zakończenia.\r\n-Przekazać informacje o ewentualnych działaniach po stronie klienta.\r\n-Poinformować, gdzie klient może sprawdzić aktualny status prac.\r\n\r\nPrzykładowy komunikat\r\n-Informujemy, że w dniu [data] w godzinach [godzina rozpoczęcia–godzina zakończenia] w Państwa lokalizacji będą prowadzone planowane prace techniczne. W tym czasie mogą występować czasowe przerwy w działaniu usługi [nazwa usługi]. Po zakończeniu prac usługa powinna zostać przywrócona automatycznie. Prosimy o cierpliwość i niewyłączanie urządzeń, chyba że otrzymają Państwo inne zalecenia.\r\n\r\nPo zakończeniu prac\r\n-Sprawdź, czy komunikat o pracach został zamknięty.\r\n-Poproś klienta o sprawdzenie działania usługi.\r\n-Jeśli usługa nie działa, zalecany jest restart urządzeń zgodnie z procedurą.\r\n-Jeżeli problem nadal występuje, utwórz osobne zgłoszenie techniczne.\r\n-Nie należy obiecywać rekompensaty ani konkretnego terminu usunięcia problemu bez potwierdzenia w systemie.\r\n\r\nZasady komunikacji\r\n-przekazuj wyłącznie potwierdzone informacje,\r\n-używaj prostego i zrozumiałego języka,\r\n-nie obwiniaj klienta ani innych działów,\r\n-nie podawaj wewnętrznych szczegółów technicznych, które nie są potrzebne,\r\n-nie gwarantuj terminu zakończenia, jeśli jest on orientacyjny,\r\n-zapisz kontakt i przekazane informacje w systemie.\r\n\r\nDziałanie końcowe\r\nJeżeli po przewidywanym zakończeniu prac usługa nadal nie działa, sprawdź aktualne komunikaty na grupie działu technicznego. Następnie wykonaj podstawową diagnostykę i utwórz zgłoszenie indywidualne, podając datę kontaktu, zakres problemu oraz informację o przeprowadzonych czynnościach.', '27712af87a10f8bb42ff4b25a04af4f8.jpg', 6, 3, 0, 0, '2026-09-04 21:12:52'),
(10, '[NIEAKTUALNE] Stara konfiguracja routera Wi‑Fi według standardu WPA [test zgłoszenia nieaktualności]', 'Poniższa procedura opisuje konfigurację sieci bezprzewodowej w starszych routerach. Może być przydatna wyłącznie dla urządzeń korzystających ze starego oprogramowania.\r\n\r\nProcedura\r\n-Zaloguj się do panelu routera pod adresem 192.168.1.1.\r\n-Przejdź do ustawień sieci bezprzewodowej.\r\n-Ustaw nazwę sieci: Telecom_WiFi.\r\n-Wybierz zabezpieczenie WPA-PSK.\r\n-Ustaw szyfrowanie TKIP.\r\n-Ustaw hasło: telecom123.\r\n-Zapisz konfigurację i uruchom ponownie router.\r\n\r\nWażna informacja\r\nProcedura została przygotowana dla starszych modeli routerów i może nie odpowiadać aktualnym zaleceniom bezpieczeństwa. Nowsze urządzenia mogą wymagać innego sposobu konfiguracji.\r\n\r\nDziałanie końcowe\r\nW przypadku problemów ze starszym routerem należy skontaktować się z pomocą techniczną.', 'a4b8f94e288a70a0669491bb88b1d8ef.jpg', 11, 3, 0, 1, '2026-09-04 21:17:39'),
(11, 'Nieprawidłowa konfiguracja VLAN dla usługi Internet', 'Opis problemu\r\nKlient nie ma dostępu do Internetu, mimo że urządzenie ONT jest zsynchronizowane, a router działa prawidłowo. W systemie sieciowym może być widoczny problem z przypisaniem właściwego VLAN-u do portu switcha.\r\n\r\nMożliwe przyczyny\r\n-przypisanie niewłaściwego VLAN-u do portu,\r\n-brak VLAN-u na porcie trunk,\r\n-błędna konfiguracja portu access,\r\n-nieprawidłowe oznaczanie ramek VLAN,\r\n-pomyłka w konfiguracji usługi klienta,\r\n-brak VLAN-u na jednym z przełączników,\r\n-niezgodność konfiguracji między switchem, OLT i routerem,\r\n-błędna konfiguracja tagowania po stronie ONT.\r\n\r\nKroki diagnostyczne\r\n-Sprawdź, czy ONT klienta ma status Online i prawidłową synchronizację PON.\r\n-Zweryfikuj, jaki VLAN powinien obsługiwać usługę Internet dla danego klienta.\r\n-Sprawdź konfigurację portu, do którego podłączony jest ONT lub switch dostępowy.\r\n-Ustal, czy port pracuje jako:\r\n    -access - dla pojedynczego VLAN-u,\r\n    -trunk - dla wielu VLAN-ów.\r\n-Sprawdź, czy wymagany VLAN znajduje się na liście dozwolonych VLAN-ów na trunku.\r\n-Zweryfikuj, czy VLAN jest prawidłowo oznaczany na całej trasie:\r\nOLT → switch → router → Internet.\r\n-Sprawdź, czy VLAN nie jest blokowany przez filtr, listę kontroli dostępu albo konfigurację portu.\r\n-Porównaj konfigurację z działającym portem lub podobnym klientem.\r\n-Sprawdź logi switcha, OLT i routera pod kątem błędów VLAN lub utraty sesji.\r\nPo zmianie konfiguracji wykonaj test połączenia i sprawdź, czy router otrzymuje adres IP.\r\n\r\nDziałanie końcowe\r\nJeżeli problem wynikał z błędnego VLAN-u:\r\n-popraw konfigurację zgodnie ze standardem operatora,\r\n-zapisz konfigurację urządzenia,\r\n-odśwież sesję lub zrestartuj urządzenia, jeśli jest to wymagane,\r\n-sprawdź dostęp do Internetu,\r\n-poinformuj klienta o przywróceniu usługi.\r\nNie należy zmieniać konfiguracji produkcyjnego switcha lub OLT bez autoryzacji. Przed zmianą należy wykonać kopię konfiguracji i zapisać poprzednie ustawienia.\r\n\r\nOczekiwany rezultat po prawidłowej konfiguracji:\r\n-ONT pozostaje w stanie Online,\r\n-router otrzymuje poprawny adres IP,\r\n-sesja Internet działa prawidłowo,\r\n-klient odzyskuje dostęp do Internetu.', '88ec1816382762e763bf7a1530a9d3b9.png', 7, 3, 0, 0, '2026-09-04 21:37:57'),
(12, 'Problemy z połączeniami wychodzącymi i przychodzącymi', 'Opis problemu\r\nKlient nie może wykonywać połączeń wychodzących, odbierać połączeń przychodzących albo połączenia są automatycznie przerywane. Problem może dotyczyć wszystkich numerów lub tylko wybranych połączeń.\r\n\r\nMożliwe przyczyny\r\n-brak zasięgu sieci komórkowej,\r\n-nieaktywna lub zablokowana karta SIM,\r\n-blokada połączeń wychodzących lub przychodzących,\r\n-nieopłacone należności albo ograniczenie usług,\r\n-włączony tryb samolotowy,\r\n-nieprawidłowe ustawienia sieci w telefonie,\r\n-problem z rejestracją telefonu w sieci,\r\n-niezgodność lub problem z usługą VoLTE,\r\n-uszkodzenie karty SIM albo telefonu,\r\n-chwilowa awaria sieci.\r\n\r\nKroki diagnostyczne\r\n-Sprawdź, czy w lokalizacji klienta występuje awaria lub problem z zasięgiem.\r\n-Upewnij się, że tryb samolotowy jest wyłączony.\r\n-Sprawdź, czy telefon pokazuje zasięg sieci.\r\n-Uruchom ponownie telefon.\r\n-Wyjmij i ponownie włóż kartę SIM, jeśli konstrukcja urządzenia na to pozwala.\r\n-Sprawdź status konta klienta i aktywność usług głosowych.\r\n-Zweryfikuj, czy nie są włączone blokady połączeń.\r\n-Sprawdź numer centrum obsługi połączeń, jeśli telefon udostępnia taką opcję.\r\n-Ustaw wybór operatora na automatyczny.\r\n-Tymczasowo wyłącz i ponownie włącz VoLTE lub połączenia przez Wi‑Fi.\r\n\r\nWykonaj test:\r\n-połączenia wychodzącego,\r\n-połączenia przychodzącego,\r\n-połączenia z innym numerem,\r\n-połączenia w innej lokalizacji, jeśli jest to możliwe.\r\n-Włóż kartę SIM do innego, sprawnego telefonu.\r\n\r\nDziałanie końcowe\r\nJeżeli karta SIM działa w innym telefonie, problem najprawdopodobniej dotyczy urządzenia lub jego konfiguracji. Jeżeli karta nie działa również w innym telefonie:\r\n-sprawdź jej status w systemie operatora,\r\n-zweryfikuj blokady usług,\r\n-sprawdź, czy karta nie wymaga wymiany\r\nNie należy przekazywać klientowi informacji o blokadach konta bez wcześniejszego potwierdzenia jego tożsamości.\r\n\r\nOczekiwany rezultat\r\nPo usunięciu blokady lub poprawieniu konfiguracji klient powinien móc wykonywać i odbierać połączenia bez przerw i komunikatów o błędach.', 'a36f03c71c69c070f291832aecd88031.jpg', 3, 3, 0, 0, '2026-09-04 21:42:47'),
(13, 'Brak połączenia z anteną LTE/5G na dachu', 'Klient korzysta z domowego Internetu dostarczanego przez zewnętrzną antenę LTE/5G zamontowaną na dachu lub elewacji. Router nie uzyskuje połączenia z siecią, a kontrolka Internetu może być wyłączona, czerwona albo migać. W panelu urządzenia może pojawić się komunikat „Brak sygnału”, „Brak rejestracji w sieci” lub „No service”.\r\n\r\nMożliwe przyczyny\r\n-brak zasilania anteny zewnętrznej,\r\n-odłączony lub uszkodzony przewód Ethernet,\r\n-problem z zasilaczem PoE,\r\n-luźne lub zawilgocone złącze,\r\n-antena jest skierowana poza zasięg nadajnika,\r\n-awaria nadajnika operatora,\r\n-uszkodzenie anteny lub routera,\r\n-nieprawidłowe ustawienia APN,\r\n-nieaktywna karta SIM lub blokada usługi.\r\n\r\nKroki diagnostyczne\r\n-Sprawdź, czy router i zasilacz PoE są podłączone do prądu.\r\n-Sprawdź kontrolki na routerze oraz zasilaczu PoE.\r\n-Zweryfikuj, czy przewód Ethernet jest podłączony do właściwych portów:\r\n    -PoE - połączenie z anteną,\r\n    -LAN - połączenie z routerem lub komputerem.\r\n-Sprawdź przewód pod kątem przetarć, zagięć i śladów wilgoci.\r\n-Nie wykonuj prac na dachu bez odpowiednich zabezpieczeń.\r\n\r\nUruchom ponownie urządzenia:\r\n-odłącz zasilanie routera i PoE,\r\n-odczekaj około 30 sekund,\r\n-podłącz ponownie PoE, a następnie router.\r\n-Zaloguj się do panelu urządzenia i sprawdź:\r\n    -status karty SIM,\r\n    -rejestrację w sieci,\r\n    -ustawienia APN,\r\n    -parametry sygnału,\r\n    -technologię połączenia LTE/5G.\r\n-Sprawdź, czy w lokalizacji nie występuje awaria nadajnika.\r\n\r\n\r\nJeżeli po restarcie antena nadal nie łączy się z siecią:\r\n-sprawdź działanie usługi w systemie operatora,\r\n-zweryfikuj status karty SIM,\r\n-sprawdź zasilacz PoE i przewód Ethernet,\r\n-zleć pomiar instalacji przez serwis,\r\n-w razie potrzeby zleć wizytę technika.\r\n\r\nNie należy samodzielnie przestawiać anteny na dachu ani otwierać urządzeń zewnętrznych.\r\n\r\nW zgłoszeniu podaj:\r\n-numer klienta,\r\n-adres instalacji,\r\n-model anteny i routera,\r\n-stan kontrolek,\r\n-status karty SIM,\r\n-komunikat błędu,\r\n-parametry sygnału,\r\n-informację o wykonanym restarcie.\r\n\r\nOczekiwany rezultat\r\nAntena powinna zarejestrować się w sieci LTE/5G, a router powinien uzyskać dostęp do Internetu. Kontrolka połączenia powinna świecić prawidłowym kolorem, zgodnie z instrukcją urządzenia.', '63c917448c6322b0f16e18e542e747fb.jpg', 12, 3, 0, 0, '2026-09-04 21:48:55'),
(14, 'test nieaktualnego artykułu', 'test nieaktualnego artykułu', '44d85203dc56f48dd21de5ef2c7776fe.jpg', 13, 3, 0, 0, '2026-09-04 21:50:39');

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `article_reactions`
--

CREATE TABLE `article_reactions` (
  `id` int(11) NOT NULL,
  `article_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `emoji` varchar(32) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `article_reactions`
--

INSERT INTO `article_reactions` (`id`, `article_id`, `user_id`, `emoji`, `created_at`) VALUES
(4, 2, 1, 'noting', '2026-09-02 19:12:54'),
(5, 2, 1, 'ratge', '2026-09-02 19:13:03'),
(6, 2, 1, 'shrug', '2026-09-02 19:13:06'),
(7, 3, 1, 'pray', '2026-09-02 19:13:12'),
(8, 3, 1, 'pog', '2026-09-02 19:13:14'),
(9, 3, 1, 'ratge', '2026-09-02 19:13:14'),
(10, 4, 1, 'noting', '2026-09-02 19:13:20'),
(11, 4, 1, 'pepethink', '2026-09-02 19:13:21'),
(12, 2, 3, 'noting', '2026-09-02 19:13:33'),
(13, 3, 3, 'noting', '2026-09-02 19:13:37'),
(14, 3, 3, 'ratge', '2026-09-02 19:13:40'),
(15, 4, 3, 'noting', '2026-09-02 19:13:44'),
(16, 4, 3, 'pog', '2026-09-02 19:13:46'),
(17, 5, 4, 'pepethink', '2026-09-04 20:54:51'),
(18, 5, 4, 'noting', '2026-09-04 20:54:52'),
(19, 6, 4, 'pepethink', '2026-09-04 20:59:43'),
(20, 6, 4, 'shrug', '2026-09-04 20:59:43'),
(21, 6, 4, 'cringe', '2026-09-04 20:59:44'),
(22, 6, 4, 'pray', '2026-09-04 20:59:44'),
(23, 7, 4, 'pog', '2026-09-04 21:05:53'),
(24, 7, 4, 'noting', '2026-09-04 21:05:54'),
(25, 8, 4, 'ratge', '2026-09-04 21:09:00'),
(26, 8, 4, 'cringe', '2026-09-04 21:09:00'),
(27, 8, 4, 'noting', '2026-09-04 21:09:01'),
(28, 8, 4, 'pog', '2026-09-04 21:09:02'),
(29, 9, 3, 'pepethink', '2026-09-04 21:12:57'),
(30, 9, 3, 'noting', '2026-09-04 21:12:58'),
(31, 8, 3, 'pog', '2026-09-04 21:13:18'),
(32, 8, 3, 'noting', '2026-09-04 21:13:22'),
(33, 5, 3, 'pepethink', '2026-09-04 21:13:34'),
(34, 5, 3, 'shrug', '2026-09-04 21:13:35'),
(35, 5, 3, 'cringe', '2026-09-04 21:13:35'),
(36, 5, 3, 'noting', '2026-09-04 21:13:36'),
(37, 6, 3, 'cringe', '2026-09-04 21:14:06'),
(38, 6, 3, 'pepethink', '2026-09-04 21:14:07'),
(39, 10, 3, 'ratge', '2026-09-04 21:17:45'),
(40, 10, 3, 'shrug', '2026-09-04 21:17:46'),
(41, 10, 3, 'pog', '2026-09-04 21:17:47'),
(42, 10, 3, 'noting', '2026-09-04 21:17:48'),
(43, 2, 4, 'noting', '2026-09-04 21:28:44'),
(44, 9, 2, 'noting', '2026-09-04 21:29:36'),
(45, 11, 3, 'pog', '2026-09-04 21:38:07'),
(46, 11, 3, 'noting', '2026-09-04 21:38:08'),
(47, 12, 3, 'noting', '2026-09-04 21:42:55'),
(48, 13, 3, 'noting', '2026-09-04 21:49:18'),
(49, 13, 3, 'ratge', '2026-09-04 21:49:20'),
(50, 13, 3, 'pog', '2026-09-04 21:49:20'),
(51, 13, 3, 'pray', '2026-09-04 21:49:22');

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `article_reports`
--

CREATE TABLE `article_reports` (
  `id` int(11) NOT NULL,
  `article_id` int(11) NOT NULL,
  `reporter_id` int(11) NOT NULL,
  `reason` text NOT NULL,
  `status` enum('open','resolved','rejected') NOT NULL DEFAULT 'open',
  `resolved_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `article_reports`
--

INSERT INTO `article_reports` (`id`, `article_id`, `reporter_id`, `reason`, `status`, `resolved_by`, `created_at`) VALUES
(1, 10, 2, 'Procedura wykorzystuje przestarzałe i niezalecane zabezpieczenia WPA/TKIP', 'resolved', 1, '2026-09-04 21:18:33'),
(2, 10, 2, 'nieaktualne', 'resolved', 1, '2026-09-04 21:24:57'),
(3, 7, 2, 'test', 'open', NULL, '2026-09-04 21:30:01');

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `article_tags`
--

CREATE TABLE `article_tags` (
  `article_id` int(11) NOT NULL,
  `tag_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `article_tags`
--

INSERT INTO `article_tags` (`article_id`, `tag_id`) VALUES
(2, 1),
(2, 2),
(3, 3),
(3, 4),
(3, 5),
(3, 6),
(4, 2),
(4, 7),
(4, 8),
(4, 9),
(5, 1),
(5, 2),
(5, 11),
(5, 12),
(5, 13),
(5, 14),
(6, 2),
(6, 7),
(6, 8),
(6, 17),
(6, 18),
(7, 3),
(7, 4),
(7, 22),
(7, 23),
(7, 25),
(8, 5),
(8, 26),
(8, 27),
(8, 28),
(8, 30),
(9, 31),
(9, 32),
(9, 33),
(9, 34),
(9, 35),
(10, 7),
(10, 8),
(10, 38),
(10, 39),
(10, 40),
(11, 25),
(11, 46),
(11, 48),
(11, 49),
(11, 50),
(12, 9),
(12, 51),
(12, 52),
(12, 53),
(12, 55),
(13, 57),
(13, 58),
(13, 59),
(13, 60),
(13, 65),
(14, 66),
(14, 67);

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `categories`
--

CREATE TABLE `categories` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`id`, `name`) VALUES
(1, 'Światłowód i FTTH'),
(2, 'Routery i Wi-Fi'),
(3, 'Telefonia komórkowa'),
(4, 'Telewizja IPTV'),
(5, 'Telewizja (DVB-T / DVB-C)'),
(6, 'Awarie masowe i komunikaty'),
(7, 'Infrastruktura sieciowa (Switche / OLT)'),
(11, 'Nieaktualne'),
(12, 'Radio/LTE'),
(13, 'Inne');

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `comments`
--

CREATE TABLE `comments` (
  `id` int(11) NOT NULL,
  `article_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `parent_comment_id` int(11) DEFAULT NULL,
  `content` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `comments`
--

INSERT INTO `comments` (`id`, `article_id`, `user_id`, `parent_comment_id`, `content`, `created_at`) VALUES
(1, 2, 3, NULL, '123', '2026-09-02 19:14:25'),
(2, 5, 4, NULL, 'testowy komentarz', '2026-09-04 20:55:03'),
(3, 6, 4, NULL, 'test', '2026-09-04 20:59:47'),
(4, 7, 4, NULL, 'komentarz', '2026-09-04 21:06:03'),
(5, 8, 4, NULL, 'komentarz', '2026-09-04 21:09:10'),
(6, 8, 3, 5, 'test', '2026-09-04 21:13:28'),
(7, 5, 3, 2, 'test', '2026-09-04 21:13:40'),
(8, 6, 3, 3, 'test2', '2026-09-04 21:13:56'),
(9, 10, 2, NULL, 'test', '2026-09-04 21:18:36'),
(10, 2, 1, 1, '321', '2026-09-04 21:27:46'),
(11, 7, 2, 4, 'test', '2026-09-04 21:30:08'),
(12, 11, 3, NULL, 'test', '2026-09-04 21:38:10'),
(13, 12, 3, NULL, 'testtt', '2026-09-04 21:42:58'),
(14, 13, 3, NULL, 'test', '2026-09-04 21:49:24');

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `favorites`
--

CREATE TABLE `favorites` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `article_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `favorites`
--

INSERT INTO `favorites` (`id`, `user_id`, `article_id`, `created_at`) VALUES
(1, 4, 6, '2026-09-04 20:59:40'),
(2, 4, 7, '2026-09-04 21:05:51'),
(3, 4, 8, '2026-09-04 21:08:57'),
(4, 3, 9, '2026-09-04 21:12:54'),
(5, 3, 8, '2026-09-04 21:13:15'),
(6, 3, 6, '2026-09-04 21:14:04'),
(7, 1, 2, '2026-09-04 21:27:38'),
(8, 4, 2, '2026-09-04 21:28:42'),
(9, 2, 8, '2026-09-04 21:29:22'),
(10, 2, 9, '2026-09-04 21:29:34'),
(11, 3, 11, '2026-09-04 21:38:05');

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `notifications`
--

CREATE TABLE `notifications` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `article_id` int(11) DEFAULT NULL,
  `comment_id` int(11) DEFAULT NULL,
  `message` varchar(255) NOT NULL,
  `is_read` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `notifications`
--

INSERT INTO `notifications` (`id`, `user_id`, `article_id`, `comment_id`, `message`, `is_read`, `created_at`) VALUES
(1, 4, NULL, 6, 'wiewiorka odpowiedział na Twój komentarz.', 1, '2026-09-04 21:13:29'),
(2, 4, NULL, 7, 'wiewiorka odpowiedział na Twój komentarz.', 1, '2026-09-04 21:13:40'),
(3, 4, NULL, 8, 'wiewiorka odpowiedział na Twój komentarz.', 1, '2026-09-04 21:13:56'),
(4, 3, 10, NULL, 'testowy zgłosił nieaktualność Twojego artykułu.', 1, '2026-09-04 21:18:33'),
(5, 1, 10, NULL, 'testowy zgłosił nieaktualność Twojego artykułu.', 1, '2026-09-04 21:18:33'),
(6, 3, NULL, 9, 'testowy dodał komentarz do Twojego artykułu.', 1, '2026-09-04 21:18:36'),
(7, 2, 10, NULL, 'Administrator oznaczył Twoje zgłoszenie jako: rozpatrzone.', 1, '2026-09-04 21:18:53'),
(8, 3, 10, NULL, 'testowy zgłosił nieaktualność Twojego artykułu.', 1, '2026-09-04 21:24:57'),
(9, 1, 10, NULL, 'testowy zgłosił nieaktualność Twojego artykułu.', 1, '2026-09-04 21:24:57'),
(10, 2, 10, NULL, 'Administrator oznaczył Twoje zgłoszenie jako: rozpatrzone.', 1, '2026-09-04 21:25:23'),
(11, 3, NULL, 10, 'admin odpowiedział na Twój komentarz.', 1, '2026-09-04 21:27:46'),
(12, 4, 7, NULL, 'testowy zgłosił nieaktualność Twojego artykułu.', 0, '2026-09-04 21:30:01'),
(13, 1, 7, NULL, 'testowy zgłosił nieaktualność Twojego artykułu.', 0, '2026-09-04 21:30:01'),
(14, 4, NULL, 11, 'testowy odpowiedział na Twój komentarz.', 0, '2026-09-04 21:30:08');

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `ratings`
--

CREATE TABLE `ratings` (
  `id` int(11) NOT NULL,
  `article_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `rating_value` tinyint(4) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `ratings`
--

INSERT INTO `ratings` (`id`, `article_id`, `user_id`, `rating_value`, `created_at`) VALUES
(11, 3, 1, 1, '2026-09-02 18:31:40'),
(12, 2, 2, 1, '2026-09-02 18:31:50'),
(13, 2, 3, 1, '2026-09-02 18:32:42'),
(14, 4, 3, 1, '2026-09-02 19:13:47'),
(15, 5, 4, 1, '2026-09-04 20:54:50'),
(16, 6, 4, 1, '2026-09-04 20:59:42'),
(17, 7, 4, 1, '2026-09-04 21:05:55'),
(18, 8, 4, 1, '2026-09-04 21:09:03'),
(19, 9, 3, 1, '2026-09-04 21:12:58'),
(20, 8, 3, 1, '2026-09-04 21:13:17'),
(22, 2, 1, 1, '2026-09-04 21:27:41'),
(23, 2, 4, 1, '2026-09-04 21:28:45'),
(24, 8, 2, 1, '2026-09-04 21:29:25'),
(25, 9, 2, 1, '2026-09-04 21:29:35'),
(26, 7, 2, 1, '2026-09-04 21:29:56'),
(27, 11, 3, 1, '2026-09-04 21:38:06'),
(28, 12, 3, 1, '2026-09-04 21:42:54'),
(29, 13, 3, 1, '2026-09-04 21:49:19');

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `tags`
--

CREATE TABLE `tags` (
  `id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `tags`
--

INSERT INTO `tags` (`id`, `name`) VALUES
(57, '5g'),
(67, 'admin'),
(18, 'adres ip'),
(58, 'antena'),
(33, 'awaria'),
(14, 'brak internetu'),
(28, 'brak kanałów'),
(60, 'brak połączenia'),
(22, 'buforowanie'),
(4, 'dekoder'),
(17, 'dhcp'),
(2, 'diagnostyka'),
(27, 'dvb-c'),
(26, 'dvb-t'),
(13, 'ftth'),
(25, 'internet'),
(59, 'internet radiowy'),
(3, 'iptv'),
(53, 'karta sim'),
(35, 'klient'),
(32, 'komunikat'),
(39, 'konfiguracja'),
(50, 'konfiguracja sieci'),
(34, 'konserwacja'),
(65, 'lte'),
(40, 'nieaktualne'),
(49, 'olt'),
(11, 'ont'),
(52, 'połączenia'),
(12, 'pon'),
(31, 'prace serwisowe'),
(7, 'router'),
(1, 'światłowód'),
(48, 'switch'),
(56, 'te'),
(51, 'telefonia'),
(5, 'telewizja'),
(66, 'test'),
(6, 'uprawnienia'),
(46, 'vlan'),
(55, 'volte'),
(8, 'wi-fi'),
(38, 'wpa'),
(30, 'wyszukiwanie'),
(23, 'zacinanie obrazu'),
(9, 'zasieg');

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` varchar(20) NOT NULL DEFAULT 'user',
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `role`, `is_active`, `created_at`) VALUES
(1, 'admin', '$2y$10$PBy2UrixQWhENa1Rv5O3te0eAfWjPPsHMymo5l0.ExNVGfrwrJDQe', 'admin', 1, '2026-09-01 22:14:30'),
(2, 'testowy', '$2y$10$u2EQwhUepwWoT.yWk5Pf8.DjJThSahkb1Jv7uqLVqtk1prL1xxEay', 'user', 1, '2026-09-02 17:51:54'),
(3, 'wiewiorka', '$2y$10$lq.Gi6MU8JSsrum/HofWq.qlyB3RNbmjd5yuBuVb.Ob2Z1RxN9TrW', 'user', 1, '2026-09-02 18:32:35'),
(4, 'robertlis', '$2y$10$NnSRKx3I7Tju93zCFs47kuZseif8MVq2/R0SlVRnf5ymBin8EbFTy', 'user', 1, '2026-09-04 20:49:24');

--
-- Indeksy dla zrzutów tabel
--

--
-- Indeksy dla tabeli `articles`
--
ALTER TABLE `articles`
  ADD PRIMARY KEY (`id`),
  ADD KEY `category_id` (`category_id`),
  ADD KEY `user_id` (`user_id`);
ALTER TABLE `articles` ADD FULLTEXT KEY `title` (`title`,`content`);

--
-- Indeksy dla tabeli `article_reactions`
--
ALTER TABLE `article_reactions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_user_article_emoji` (`article_id`,`user_id`,`emoji`),
  ADD KEY `user_id` (`user_id`);

--
-- Indeksy dla tabeli `article_reports`
--
ALTER TABLE `article_reports`
  ADD PRIMARY KEY (`id`),
  ADD KEY `article_id` (`article_id`),
  ADD KEY `reporter_id` (`reporter_id`),
  ADD KEY `resolved_by` (`resolved_by`);

--
-- Indeksy dla tabeli `article_tags`
--
ALTER TABLE `article_tags`
  ADD PRIMARY KEY (`article_id`,`tag_id`),
  ADD KEY `tag_id` (`tag_id`);

--
-- Indeksy dla tabeli `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`);

--
-- Indeksy dla tabeli `comments`
--
ALTER TABLE `comments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `article_id` (`article_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `parent_comment_id` (`parent_comment_id`);

--
-- Indeksy dla tabeli `favorites`
--
ALTER TABLE `favorites`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_user_article_favorite` (`user_id`,`article_id`),
  ADD KEY `article_id` (`article_id`);

--
-- Indeksy dla tabeli `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `article_id` (`article_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `comment_id` (`comment_id`);

--
-- Indeksy dla tabeli `ratings`
--
ALTER TABLE `ratings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_user_article_rating` (`user_id`,`article_id`),
  ADD KEY `article_id` (`article_id`);

--
-- Indeksy dla tabeli `tags`
--
ALTER TABLE `tags`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indeksy dla tabeli `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `articles`
--
ALTER TABLE `articles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `article_reactions`
--
ALTER TABLE `article_reactions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=52;

--
-- AUTO_INCREMENT for table `article_reports`
--
ALTER TABLE `article_reports`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `comments`
--
ALTER TABLE `comments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `favorites`
--
ALTER TABLE `favorites`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `ratings`
--
ALTER TABLE `ratings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=30;

--
-- AUTO_INCREMENT for table `tags`
--
ALTER TABLE `tags`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=68;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `articles`
--
ALTER TABLE `articles`
  ADD CONSTRAINT `articles_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `articles_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `article_reactions`
--
ALTER TABLE `article_reactions`
  ADD CONSTRAINT `article_reactions_ibfk_1` FOREIGN KEY (`article_id`) REFERENCES `articles` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `article_reactions_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `article_reports`
--
ALTER TABLE `article_reports`
  ADD CONSTRAINT `article_reports_ibfk_1` FOREIGN KEY (`article_id`) REFERENCES `articles` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `article_reports_ibfk_2` FOREIGN KEY (`reporter_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `article_reports_ibfk_3` FOREIGN KEY (`resolved_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `article_tags`
--
ALTER TABLE `article_tags`
  ADD CONSTRAINT `article_tags_ibfk_1` FOREIGN KEY (`article_id`) REFERENCES `articles` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `article_tags_ibfk_2` FOREIGN KEY (`tag_id`) REFERENCES `tags` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `comments`
--
ALTER TABLE `comments`
  ADD CONSTRAINT `comments_ibfk_1` FOREIGN KEY (`article_id`) REFERENCES `articles` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `comments_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `comments_ibfk_3` FOREIGN KEY (`parent_comment_id`) REFERENCES `comments` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `favorites`
--
ALTER TABLE `favorites`
  ADD CONSTRAINT `favorites_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `favorites_ibfk_2` FOREIGN KEY (`article_id`) REFERENCES `articles` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `notifications_ibfk_2` FOREIGN KEY (`comment_id`) REFERENCES `comments` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `notifications_ibfk_3` FOREIGN KEY (`article_id`) REFERENCES `articles` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `ratings`
--
ALTER TABLE `ratings`
  ADD CONSTRAINT `ratings_ibfk_1` FOREIGN KEY (`article_id`) REFERENCES `articles` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `ratings_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

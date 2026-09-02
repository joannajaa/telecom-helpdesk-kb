# telecom-helpdesk-kb

# Telecom Helpdesk Knowledge Base (KB)

## 1. Opis projektu i cel
Telecom Helpdesk KB to webowa baza wiedzy stworzona z myślą o wewnętrznym dziale wsparcia technicznego (Helpdesk) w branży telekomunikacyjnej. 

Głównym celem aplikacji jest:
* Skrócenie czasu obsługi zgłoszeń (AHT): Umożliwienie konsultantom błyskawicznego odnajdywania gotowych procedur, kodów błędów i schematów rozwiązywania problemów z usługami (np. światłowód, telewizja cyfrowa, routery).
* Centralizacja i standaryzacja wiedzy: Eliminacja rozproszonych notatek na rzecz jednolitej bazy instrukcji technicznych.
* Weryfikacja jakości procedur: Ocena przydatności instrukcji przez pracowników za pomocą mechanizmu polubień w czasie rzeczywistym (AJAX).

## 2. Architektura i technologie
Aplikacja została zbudowana w modelu klient-serwer z podziałem na warstwę prezentacji, logiki biznesowej i danych.

### Technologie:
* Backend: PHP 8 (obiektowy interfejs PDO)
* Baza danych: MySQL / MariaDB
* Frontend: HTML5, CSS3 (CSS Variables), JavaScript (Fetch API)
* Środowisko lokalne: XAMPP (Apache + MySQL)

### Kluczowe decyzje architektoniczne:
* PHP Data Objects (PDO): Zastosowano zapytania przygotowane (prepared statements), co zapewnia ochronę przed atakami typu SQL Injection.
* Architektura modułowa: Wspólne elementy interfejsu (nagłówek, stopka, nawigacja) oraz połączenie z bazą danych zostały wydzielone do katalogu `includes/`.
* Asynchroniczność (Fetch API): Moduł oceniania artykułów działa bez przeładowywania strony.
* Arkusz stylów: Oparty o zmienne CSS, co umożliwia dynamiczną zmianę motywów (jasny, ciemny z niebieskim akcentem, ciemny z różowym akcentem) bez duplikowania kodu.

## 3. Struktura bazy danych
Baza danych `telecom_kb` składa się z 4 powiązanych ze sobą tabel:

### Schemat tabel:
* users – przechowuje konta konsultantów i administratorów.
  * `id` (INT, PK, AUTO_INCREMENT)
  * `username` (VARCHAR, UNIQUE)
  * `password` (VARCHAR, hash bcrypt)
  * `role` (ENUM: 'user', 'admin')
  * `created_at` (DATETIME)

* categories – działy tematyczne zgłoszeń technicznych.
  * `id` (INT, PK, AUTO_INCREMENT)
  * `name` (VARCHAR, UNIQUE)
  * `created_at` (DATETIME)

* articles – baza procedur technicznych.
  * `id` (INT, PK, AUTO_INCREMENT)
  * `title` (VARCHAR)
  * `content` (TEXT)
  * `image` (VARCHAR, NULLable)
  * `category_id` (INT, FK -> categories.id)
  * `user_id` (INT, FK -> users.id)
  * `created_at` (DATETIME)

* ratings – system oceniania przydatności artykułów.
  * `id` (INT, PK, AUTO_INCREMENT)
  * `article_id` (INT, FK -> articles.id)
  * `user_id` (INT, FK -> users.id)
  * `rating_value` (TINYINT)
  * `created_at` (DATETIME)

### Relacje między tabelami:
* Kategoria -> Artykuły (1:N)
* Użytkownik -> Artykuły (1:N)
* Artykuł -> Oceny (1:N)
* Unikalna para `article_id` + `user_id` gwarantuje, że użytkownik może ocenić wpis tylko raz.

## 4. Instrukcja instalacji i uruchomienia

### Wymagania wstępne:
* Pakiet XAMPP (Apache + MySQL/MariaDB, PHP 8.x)
* Przeglądarka internetowa

### Krok 1: Kopiowanie plików aplikacji
1. Skopiuj katalog projektu do folderu:
   `C:\xampp\htdocs\telecom_kb`

### Krok 2: Import bazy danych
1. Uruchom Apache oraz MySQL w XAMPP Control Panel.
2. Otwórz w przeglądarce adres: `http://localhost/phpmyadmin/`.
3. Utwórz nową bazę danych o nazwie `telecom_kb` z kodowaniem `utf8mb4_polish_ci`.
4. Przejdź do zakładki Importuj, wybierz plik `database.sql` i zatwierdź import.

### Krok 3: Weryfikacja połączenia
W pliku `includes/db.php` upewnij się, że parametry połączenia odpowiadają konfiguracji:
* Host: `localhost`
* Baza: `telecom_kb`
* Użytkownik: `root`
* Hasło: puste

### Krok 4: Uruchomienie aplikacji
Otwórz w przeglądarce adres:
`http://localhost/telecom_kb/index.php`

## 5. Konta testowe

| Rola | Login | Hasło | Zakres uprawnień |
| :--- | :--- | :--- | :--- |
| Administrator | `admin` | `admin1234` | Pełny dostęp do bazy wiedzy, zarządzanie artykułami i kategoriami |
| Konsultant | `testowy` | `admin1234` | Przeglądanie bazy, dodawanie artykułów, system oceniania |

## 6. Główne funkcjonalności systemu
* Bezpieczna autoryzacja: Logowanie i rejestracja oparte o `password_hash` / `password_verify` oraz sesje PHP.
* Pełny moduł CRUD artykułów: Tworzenie, przeglądanie, edycja i usuwanie wpisów wraz z obsługą załączników graficznych.
* Kategoryzacja wpisów: Przypisywanie instrukcji do określonych działów tematycznych.
* Asynchroniczny system ocen: Możliwość głosowania bez odświeżania strony (AJAX/Fetch API) z blokadą wielokrotnego głosu.
* Trzy motywy wizualne: Przełącznik motywów (Jasny, Dark + Blue, Dark + Pink) zapisujący stan w `localStorage`.
* Zabezpieczenia: Ochrona przed SQL Injection (PDO prepared statements) oraz XSS (`htmlspecialchars`).

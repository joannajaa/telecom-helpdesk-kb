# Telecom Helpdesk Knowledge Base (KB)

## 1. Opis projektu i cel
Telecom Helpdesk KB to webowa baza wiedzy stworzona z myślą o wewnętrznym dziale wsparcia technicznego (Helpdesk) w branży telekomunikacyjnej. 

Głównym celem aplikacji jest:
* Skrócenie czasu obsługi zgłoszeń (AHT): Umożliwienie konsultantom błyskawicznego odnajdywania gotowych procedur, kodów błędów i schematów rozwiązywania problemów z usługami (np. światłowód, telewizja cyfrowa, routery).
* Centralizacja i standaryzacja wiedzy: Eliminacja rozproszonych notatek na rzecz jednolitej bazy instrukcji technicznych.
* Weryfikacja jakości procedur: Ocena przydatności instrukcji przez pracowników za pomocą mechanizmu polubień oraz reakcji obrazkowych w czasie rzeczywistym (AJAX).
* Wymiana doświadczeń technicznych: Dodawanie notatek serwisowych i wskazówek bezpośrednio pod procedurami.

## 2. Architektura i technologie
Aplikacja została zbudowana w modelu klient-serwer z podziałem na warstwę prezentacji, logiki biznesowej i danych.

### Technologie:
* Backend: PHP 8 (obiektowy interfejs PDO)
* Baza danych: MySQL / MariaDB
* Frontend: HTML5, CSS3 (CSS Variables), JavaScript (Fetch API)
* Środowisko lokalne: XAMPP (Apache + MySQL)

### Kluczowe decyzje architektoniczne:
* PHP Data Objects (PDO): Zastosowano zapytania przygotowane (prepared statements), co zapewnia ochronę przed atakami typu SQL Injection.
* Ochrona sesji i formularzy: Implementacja tokenów CSRF (`hash_equals`) zabezpieczających żądania POST, akcje usuwania oraz żądania AJAX.
* Architektura modułowa: Wspólne elementy interfejsu (nagłówek, stopka, nawigacja) oraz połączenie z bazą danych zostały wydzielone do katalogu `includes/`.
* Asynchroniczność (Fetch API): Moduły oceniania artykułów oraz dodawania reakcji emotkami działają w czasie rzeczywistym bez przeładowywania strony.
* Wyszukiwanie pełnotekstowe: Zastosowanie indeksu `FULLTEXT` (`MATCH...AGAINST`) w tabeli artykułów.
* Style i widok druku: Zmienne CSS umożliwiające obsługę trzech motywów kolorystycznych oraz dedykowane reguły `@media print` przygotowujące instrukcję do czystego wydruku.

## 3. Struktura bazy danych
Baza danych `telecom_kb` składa się z 11 powiązanych ze sobą tabel:

### Schemat tabel:
* users – przechowuje konta konsultantów i administratorów.
  * `id` (INT, PK, AUTO_INCREMENT)
  * `username` (VARCHAR, UNIQUE)
  * `password` (VARCHAR, hash bcrypt)
  * `role` (VARCHAR: 'user' lub 'admin')
  * `is_active` (TINYINT(1), DEFAULT 1)
  * `created_at` (TIMESTAMP)

* categories – działy tematyczne zgłoszeń technicznych.
  * `id` (INT, PK, AUTO_INCREMENT)
  * `name` (VARCHAR)

* articles – baza procedur technicznych.
  * `id` (INT, PK, AUTO_INCREMENT)
  * `title` (VARCHAR)
  * `content` (TEXT, FULLTEXT)
  * `image` (VARCHAR, NULLable)
  * `category_id` (INT, FK -> categories.id)
  * `user_id` (INT, FK -> users.id)
  * `is_pinned` (TINYINT(1), DEFAULT 0)
  * `is_archived` (TINYINT(1), DEFAULT 0)
  * `created_at` (TIMESTAMP)

* ratings – system oceniania przydatności artykułów.
  * `id` (INT, PK, AUTO_INCREMENT)
  * `article_id` (INT, FK -> articles.id)
  * `user_id` (INT, FK -> users.id)
  * `rating_value` (TINYINT)
  * `created_at` (TIMESTAMP)

* article_reactions – asynchroniczne reakcje graficzne do wpisów.
  * `id` (INT, PK, AUTO_INCREMENT)
  * `article_id` (INT, FK -> articles.id)
  * `user_id` (INT, FK -> users.id)
  * `emoji` (VARCHAR)
  * `created_at` (TIMESTAMP)

* comments – uwagi i notatki serwisowe konsultantów pod procedurami.
  * `id` (INT, PK, AUTO_INCREMENT)
  * `article_id` (INT, FK -> articles.id)
  * `user_id` (INT, FK -> users.id)
  * `content` (TEXT)
  * `parent_comment_id` (INT, nullable, FK -> comments.id)
  * `created_at` (TIMESTAMP)

* tags – słownik tagów artykułów.
  * `id` (INT, PK, AUTO_INCREMENT)
  * `name` (VARCHAR, UNIQUE)

* article_tags – tabela pośrednia relacji artykułów i tagów.
  * `article_id` (INT, FK -> articles.id)
  * `tag_id` (INT, FK -> tags.id)

* favorites – zapisane przez użytkowników ulubione artykuły.
  * `id` (INT, PK, AUTO_INCREMENT)
  * `user_id` (INT, FK -> users.id)
  * `article_id` (INT, FK -> articles.id)
  * `created_at` (TIMESTAMP)

* notifications – powiadomienia o komentarzach, odpowiedziach i zgłoszeniach.
  * `id` (INT, PK, AUTO_INCREMENT)
  * `user_id` (INT, FK -> users.id)
  * `article_id` (INT, nullable, FK -> articles.id)
  * `comment_id` (INT, nullable, FK -> comments.id)
  * `is_read` (TINYINT(1))

* article_reports – zgłoszenia nieaktualnych artykułów.
  * `id` (INT, PK, AUTO_INCREMENT)
  * `article_id` (INT, FK -> articles.id)
  * `reporter_id` (INT, FK -> users.id)
  * `reason` (TEXT)
  * `status` (ENUM: open, resolved, rejected)

### Relacje między tabelami:
* categories -> articles (1:N)
* users -> articles (1:N)
* articles -> ratings (1:N)
* users -> ratings (1:N)
* articles -> article_reactions (1:N)
* users -> article_reactions (1:N)
* articles -> comments (1:N)
* users -> comments (1:N)
* articles <-> tags (N:M)
* users -> favorites (1:N)
* articles -> favorites (1:N)
* users -> notifications (1:N)
* comments -> notifications (1:N)
* articles -> article_reports (1:N)
* users -> article_reports (1:N)

## 4. Instrukcja instalacji i uruchomienia

### Wymagania wstępne:
* Pakiet XAMPP (Apache + MySQL/MariaDB, PHP 8.x)
* Przeglądarka internetowa

### Krok 1: Kopiowanie plików aplikacji
1. Skopiuj katalog projektu do folderu serwera XAMPP, np.:
   `C:\xampp\htdocs\telecom-kb`

### Krok 2: Baza danych
1. Uruchom Apache oraz MySQL w XAMPP Control Panel.
2. Otwórz w przeglądarce adres: `http://localhost/phpmyadmin/`.
3. Użyj istniejącej bazy `telecom_kb` albo utwórz ją z kodowaniem `utf8mb4_unicode_ci`.
4. Jeśli baza pochodzi z wcześniejszej wersji projektu, wykonaj skrypt aktualizacyjny podany w dokumentacji projektu.

### Krok 3: Weryfikacja połączenia
W pliku `includes/db.php` upewnij się, że parametry połączenia odpowiadają konfiguracji serwera:
* Host: `localhost`
* Baza: `telecom_kb`
* Użytkownik: `root`
* Hasło: puste

### Krok 4: Uruchomienie aplikacji
Otwórz w przeglądarce adres:
`http://localhost/telecom-kb/index.php`

## 5. Konta testowe

| Rola | Login | Hasło | Zakres uprawnień |
| :--- | :--- | :--- | :--- |
| Administrator | `admin` | `admin1234` | Przypinanie procedur oraz zarządzanie własnymi i wszystkimi wpisami |
| Konsultant | `testowy` | `admin1234` | Przeglądanie bazy, dodawanie wpisów, komentarze, reakcje oraz system oceniania |

## 6. Główne funkcjonalności systemu
* Bezpieczna autoryzacja: Rejestracja i logowanie oparte o bezpieczne haszowanie haseł (`password_hash` z algorytmem domyślnym/bcrypt) oraz zarządzanie sesją PHP.
* Pełny moduł CRUD artykułów: Tworzenie, przeglądanie, edycja i usuwanie wpisów technicznych.
* Obsługa załączników graficznych: Bezpieczny upload plików graficznych (walidacja typów MIME przez Fileinfo, limit rozmiaru do 2 MB, unikalne nazwy).
* Kategoryzacja i szybka nawigacja: Podział na działy oraz możliwość bezpośredniego filtrowania listy z poziomu podglądu artykułu.
* Tagi artykułów: Możliwość przypisywania wielu tagów do procedury oraz filtrowania po kliknięciu tagu.
* Ulubione artykuły: Możliwość zapisywania artykułów i przeglądania ich na osobnej stronie.
* Wyszukiwanie i sortowanie: Wyszukiwanie pełnotekstowe w tytułach i treściach, filtrowanie po działach, sortowanie według daty lub popularności oraz paginacja wyników. Podczas wpisywania tekstu lista wyników jest dodatkowo odświeżana asynchronicznie.
* Przypinanie procedur awaryjnych: Możliwość oznaczenia wpisu jako przypiętego (`is_pinned`) przez administratora, co pozycjonuje go na górze listy.
* Archiwizacja artykułów: Administrator może oznaczyć nieaktualny artykuł jako archiwalny.
* Zgłaszanie nieaktualności: Użytkownicy mogą zgłaszać artykuły autorowi i administratorom, a zgłoszenia są obsługiwane w panelu administratora.
* Oznaczanie nieaktualnych artykułów: Po rozpatrzeniu zgłoszenia administrator przenosi artykuł do kategorii „Nieaktualne”, dodaje tag `nieaktualne`, oznacza tytuł i zeruje oceny, zachowując ulubione użytkowników.
* Panel administratora: Statystyki, zarządzanie rolami i aktywnością użytkowników oraz dodawanie/usuwanie pustych kategorii. Moderacja komentarzy odbywa się bezpośrednio pod odpowiednimi artykułami.
* Asynchroniczny system ocen (AJAX): Ocenianie przydatności procedury bez przeładowania strony z blokadą wielokrotnego głosu.
* System reakcji graficznych (AJAX): Możliwość reagowania na wpisy wybranymi emotkami w czasie rzeczywistym.
* Notatki techniczne: Moduł komentarzy pod procedurami chroniony przed atakami CSRF i XSS, z edycją i usuwaniem własnych komentarzy oraz usuwaniem komentarzy przez administratora.
* Powiadomienia: Rozwijana lista w nagłówku z licznikiem nieprzeczytanych powiadomień o nowych komentarzach i odpowiedziach.
* Personalizacja interfejsu: Trzy wbudowane motywy kolorystyczne (Jasny, Dark Blue, Dark Pink) z zapamiętywaniem wyboru w `localStorage`.
* Widok do druku: Dedykowany arkusz stylów drukarskich ukrywający elementy nawigacyjne i interaktywne na potrzeby fizycznego wydruku procedury.
* Zabezpieczenia: Ochrona przed SQL Injection (Prepared Statements PDO), XSS (`htmlspecialchars`) oraz CSRF (tokeny sesyjne w formularzach, usuwaniu i AJAX).

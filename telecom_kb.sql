-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Wrz 02, 2026 at 09:47 PM
-- Wersja serwera: 10.4.32-MariaDB
-- Wersja PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";
CREATE DATABASE IF NOT EXISTS `telecom_kb` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `telecom_kb`;

CREATE TABLE `tags` (
  `id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `article_tags` (
  `article_id` int(11) NOT NULL,
  `tag_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `tags` (`id`, `name`) VALUES
(1, 'światłowód'),
(2, 'diagnostyka'),
(3, 'iptv'),
(4, 'dekoder'),
(5, 'telewizja'),
(6, 'uprawnienia'),
(7, 'router'),
(8, 'wi-fi'),
(9, 'zasieg');

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
(4, 9);


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
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `articles`
--

INSERT INTO `articles` (`id`, `title`, `content`, `image`, `category_id`, `user_id`, `is_pinned`, `created_at`) VALUES
(2, 'Czerwona dioda LOS na terminalu ONT', 'Klient zgłasza całkowity brak dostępu do internetu. Na terminalu optycznym (ONT/GPON) dioda oznaczona jako LOS (Loss of Signal) świeci lub miga na czerwono, a dioda PON jest zgaszona. Oznacza to brak sygnału optycznego docierającego z centrali (OLT).\r\n\r\nKroki diagnostyczne dla konsultanta:\r\n1. Zweryfikuj w systemie bilingowo-technicznym, czy w rejonie klienta nie występuje awaria masowa (prace modernizacyjne, uszkodzenie magistrali światłowodowej).\r\n\r\n2. Sprawdź status konta abonenta (brak blokady za płatności).\r\n\r\n3. Poproś klienta o lokalizację zielonego złącza światłowodowego (patchcord SC/APC) z tyłu lub u dołu urządzenia ONT.\r\n\r\nProcedura rozwiązania problemu:\r\n\r\n1. Weryfikacja okablowania klienta:\r\nUpewnij się, że żółty przewód światłowodowy nie jest mocno zagięty (promień gięcia nie może być mniejszy niż 3 cm), przycięty drzwiami lub uszkodzony mechanicznie.\r\n\r\n2. Poinstruuj klienta, aby ostrożnie wyjął wtyk optyczny, przedmuchał gniazdo i wpiął wtyczkę ponownie, aż usłyszy charakterystyczne kliknięcie zatrzasku.\r\n\r\n3. Restart urządzenia:\r\nWyłącz terminal zasilaczem z gniazdka na 30 sekund i włącz ponownie. Odczekaj 2 minuty na próbę ponownej synchronizacji.\r\n\r\n4. Działanie końcowe:\r\nJeśli po 2 minutach dioda LOS nadal świeci na czerwono, problem leży w linii napowietrznej/ziemnej lub w szafie dystrybucyjnej.\r\n\r\nDyspozycja: Załóż zgłoszenie techniczne do serwisu terenowego z kodem: Uszkodzenie traktu optycznego/brak mocy optycznej.', 'img_6a9866f945a239.76827209.jpg', 1, 1, 0, '2026-09-02 18:12:09'),
(3, 'Diagnostyka błędu braku uprawnień i problemów z odtwarzaniem kanałów w usłudze IPTV', 'Klient zgłasza brak możliwości oglądania kanałów telewizyjnych. Na ekranie telewizora wyświetla się biały obraz lub komunikat systemowy: „Błąd autoryzacji / Brak uprawnień do danego pakietu” albo „Błąd strumienia (kod: E-401 / E-502)”. Internet na innych urządzeniach może działać prawidłowo.\r\n\r\nKroki diagnostyczne dla konsultanta:\r\n1. Sprawdź w systemie bilingowym stan pakietów telewizyjnych abonenta oraz status płatności (brak blokady windykacyjnej na usługę TV).\r\n\r\n2. Zweryfikuj w panelu zarządzania dekoderami, czy urządzenie klienta ma status Online i czy przypisany jest poprawny profil subskrypcji (poprawny VLAN).\r\n\r\n3. Sprawdź, czy dekoder łączy się z routerem przewodowo (kabel Ethernet RJ45), czy bezprzewodowo (Wi-Fi).\r\n\r\nProcedura rozwiązania problemu:\r\n1. Restart i weryfikacja połączenia sieciowego:\r\n-Poinstruuj klienta, aby sprawdził połączenie kabla Ethernet między routerem a dekoderem (dioda portu LAN na routerze powinna mrugać). Jeśli dekoder jest włączony, a połączenie kablowe nie działa, kabel ethernetowy może być uszkodzony.\r\n-Zalecana kolejność restartu: najpierw zrestartuj ONT, następnie router i poczekaj na pełną synchronizację (diody internetu/PON stałe), następnie odłącz zasilanie dekodera na 20 sekund.\r\n\r\n2. Wymuszenie odświeżenia uprawnień (Push Provisioning):\r\n-Z poziomu systemu konsultanta wyślij ponowną autoryzację pakietu\r\n\r\n3. Przywrócenie ustawień fabrycznych dekodera (opcjonalnie)\r\nhttps://www.youtube.com/watch?v=M3ut7ZiHG7M\r\n\r\n4. Działanie końcowe:\r\nJeśli dekoder nie pobiera adresu IP z routera lub zgłasza błąd sprzętowy pamięci/karty: załóż zlecenie wymiany urządzenia w punkcie obsługi (BOA) lub wyślij zgłoszenie do serwisu.', 'img_6a986b6eb59a34.53301701.jpg', 4, 1, 0, '2026-09-02 18:31:10'),
(4, 'Optymalizacja zasięgu Wi-Fi: diagnostyka zakłóceń i konfiguracja pasm 2.4 GHz oraz 5 GHz', 'Klient zgłasza częste zrywanie połączenia bezprzewodowego, niski transfer (znacznie niższy niż przepustowość łącza na umowie) lub gwałtowny spadek prędkości w innych pomieszczeniach. Często dotyczy to mieszkań w blokach lub domów o grubszych ścianach.\r\n\r\nKluczowe różnice między pasmami:\r\n🌐 Pasmo 2.4 GHz: Lepsza propagacja fal (większy zasięg, lepsze przenikanie przez ściany), ale mniejsza maksymalna przepustowość i duże ryzyko zakłóceń od sąsiednich sieci, mikrofalówek czy urządzeń Bluetooth.\r\n🚀 Pasmo 5 GHz: Bardzo wysoka przepustowość i znacznie mniejsze zatłoczenie kanałów, lecz wyraźnie mniejszy zasięg fizyczny i słabe przenikanie przez przeszkody budowlane.\r\n\r\nKroki diagnostyczne dla konsultanta:\r\n1. Sprawdź, czy problem występuje na wszystkich urządzeniach (smartfon, laptop), czy tylko na jednym (co wskazuje na problem z kartą sieciową klienta).\r\n2. Zweryfikuj prędkość po kablu Ethernet (Speedtest na PC), aby wykluczyć usterkę na linii dosyłowej lub uszkodzenie portu WAN \r\n3. Zaloguj się zdalnie do routera lub ONT i sprawdź aktualnie nadawane pasma, zajętość kanałów oraz liczbę podłączonych urządzeń.\r\n\r\nProcedura optymalizacji i konfiguracji:\r\n1. Fizyczna lokalizacja routera:\r\n-Poinstruuj klienta, aby router stał na otwartej przestrzeni, na wysokości ok. 1–1.5 m (nie w szafce, za telewizorem ani na podłodze).\r\n-Upewnij się, że urządzenie nie znajduje się bezpośrednio obok innych źródeł fal radiowych lub dużych metalowych przeszkód.\r\n2.Rozdzielenie pasm (oddzielne SSID):\r\n-W ustawieniach routera wyłącz wspólną nazwę sieci (funkcję Smart Connect/Band Steering), jeśli sprawia ona problem starszym urządzeniom.\r\n3. Skonfiguruj dwie odrębne sieci, np.:\r\nNazwaSieci_2.4GHz (dla urządzeń oddalonych, smart home, drukarek)\r\nNazwaSieci_5GHz (dla laptopów, konsol i telefonów używanych bliżej routera)\r\n4. Wybór optymalnego kanału:\r\n-Dla pasma 2.4 GHz ustaw ręcznie jeden z nienakładających się kanałów: 1, 6 lub 11 (o szerokości pasma 20 MHz, co redukuje zakłócenia).\r\n-Dla pasma 5 GHz wybierz kanał z zakresu 36–48 o szerokości 80 MHz w celu uzyskania maksymalnej przepustowości.\r\n\r\nZalecenia końcowe:\r\nJeśli metraż lokalu przekracza efektywny zasięg jednego routera (np. dom piętrowy), zaproponuj rozbudowę instalacji o punkty dostępowe (Access Point) lub system Mesh spięty szkieletem kablowym Ethernet.', 'img_6a986cf6647b75.06353853.jpg', 2, 3, 0, '2026-09-02 18:37:42');

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
(16, 4, 3, 'pog', '2026-09-02 19:13:46');

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
(7, 'Infrastruktura sieciowa (Switche / OLT)');

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `comments`
--

CREATE TABLE `comments` (
  `id` int(11) NOT NULL,
  `article_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `content` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `comments`
--

INSERT INTO `comments` (`id`, `article_id`, `user_id`, `content`, `created_at`) VALUES
(1, 2, 3, '123', '2026-09-02 19:14:25');

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
(10, 2, 1, 1, '2026-09-02 18:23:18'),
(11, 3, 1, 1, '2026-09-02 18:31:40'),
(12, 2, 2, 1, '2026-09-02 18:31:50'),
(13, 2, 3, 1, '2026-09-02 18:32:42'),
(14, 4, 3, 1, '2026-09-02 19:13:47');

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
(3, 'wiewiorka', '$2y$10$lq.Gi6MU8JSsrum/HofWq.qlyB3RNbmjd5yuBuVb.Ob2Z1RxN9TrW', 'user', 1, '2026-09-02 18:32:35');

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
  ADD KEY `user_id` (`user_id`);

--
-- Indeksy dla tabeli `ratings`
--
ALTER TABLE `ratings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_user_article_rating` (`user_id`,`article_id`),
  ADD KEY `article_id` (`article_id`);

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `article_reactions`
--
ALTER TABLE `article_reactions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `comments`
--
ALTER TABLE `comments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `ratings`
--
ALTER TABLE `ratings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `articles`
--
ALTER TABLE `articles`
  ADD CONSTRAINT `articles_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `articles_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

ALTER TABLE `tags`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

ALTER TABLE `article_tags`
  ADD PRIMARY KEY (`article_id`,`tag_id`),
  ADD KEY `tag_id` (`tag_id`);

--
-- Constraints for table `article_reactions`
--
ALTER TABLE `article_reactions`
  ADD CONSTRAINT `article_reactions_ibfk_1` FOREIGN KEY (`article_id`) REFERENCES `articles` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `article_reactions_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `comments`
--
ALTER TABLE `comments`
  ADD CONSTRAINT `comments_ibfk_1` FOREIGN KEY (`article_id`) REFERENCES `articles` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `comments_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `ratings`
--
ALTER TABLE `ratings`
  ADD CONSTRAINT `ratings_ibfk_1` FOREIGN KEY (`article_id`) REFERENCES `articles` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `ratings_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

ALTER TABLE `article_tags`
  ADD CONSTRAINT `article_tags_ibfk_1` FOREIGN KEY (`article_id`) REFERENCES `articles` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `article_tags_ibfk_2` FOREIGN KEY (`tag_id`) REFERENCES `tags` (`id`) ON DELETE CASCADE;

ALTER TABLE `tags`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

-- *********************************************
-- * SQL MySQL generation                      
-- *--------------------------------------------
-- * DB-MAIN version: 11.0.2              
-- * Generator date: Sep 14 2021              
-- * Generation date: Thu May 22 09:48:38 2025 
-- * LUN file: C:\Users\stefa\Desktop\UNIVERSITA'\basi di dati\progetto basi di dati.lun 
-- * Schema: dbphoto/1 
-- ********************************************* 


-- Database Section
-- ________________ 

-- Database Section
-- ________________ 
DROP DATABASE IF EXISTS dbphoto;

CREATE DATABASE IF NOT EXISTS dbphoto;

use dbphoto;


-- Tables Section
-- _____________ 

create table CATEGORIA (
     ID_CATEGORIA int not null,
     NOME varchar(50) not null,
	primary key (ID_CATEGORIA));

create table CRITERIO_DA_VALUTARE (
     ID_CRITERIO int not null,
     NOME varchar(50) not null,
     primary key (ID_CRITERIO));

create table GENERAZIONE_STATO (
     CODICE_STATO_ORDINE int not null,
     ID_ORDINE int not null,
     DATA_INIZIO_STATO date not null,
     primary key (ID_ORDINE, CODICE_STATO_ORDINE));
     
create table INCLUDE (
     ID_PRODOTTO int not null,
     ID_ORDINE int not null,
     QUANTITA int not null,
     primary key (ID_ORDINE, ID_PRODOTTO));

create table INDIRIZZI_DI_SPEDIZIONE (
     ID_INDIRIZZO int not null,
     VIA varchar(50) not null,
     CITTA varchar(50) not null,
     CAP varchar(50) not null,
     PROVINCIA varchar(50) not null,
     REGIONE varchar(50) not null,
     ID_UTENTE int not null,
     primary key (ID_INDIRIZZO));

create table LABORATORIO (
     ID_LABORATORIO int not null,
     NOME varchar(50) not null,
     CITTA varchar(50) not null,
     INDIRIZZO varchar(40) not null,
     REGIONE varchar(40) not null,
     PROVINCIA varchar(50) not null,
     CAP varchar(50) not null,
     primary key (ID_LABORATORIO));

create table ORDINE (
     ID_ORDINE int not null,
     ID_PAGAMENTO int,
     DATA_ORDINE date not null,
     TIPO_ORDINE varchar(50) not null,
     ID_LABORATORIO  int,
     ID_INDIRIZZO int not null,
     ID_UTENTE int not null,
	 IMPORTO DECIMAL(10, 2),
     primary key (ID_ORDINE),
     unique (ID_PAGAMENTO));

create table PAGAMENTO (
     ID_PAGAMENTO int not null,
     METODO_DI_PAGAMENTO varchar(20) not null,
     VALUTA varchar(50) not null,
     primary key (ID_PAGAMENTO));

create table PRODOTTO (
     ID_PRODOTTO int not null,
     NOME varchar(50) not null,
     MARCA varchar(50) not null,
     MODELLO varchar(50) not null,
     PREZZO varchar(50) not null,
     DESCRIZIONE varchar(50) not null,
     ID_CATEGORIA int not null,
     primary key (ID_PRODOTTO));

create table RECENSIONE (
     ID_RECENSIONE int not null,
     ID_ORDINE int not null,
     DATA date not null,
     COMMENTO varchar(255) not null,
     primary key (ID_RECENSIONE),
     unique (ID_ORDINE));

create table RULLINO (
     ID_RULLINO int not null,
     NUMERO_SCATTI int not null,
     RISOLUZIONE varchar(50) not null,
     ID_SERVIZIO int not null,
     ID_ORDINE int not null,
     primary key (ID_RULLINO));

create table SERVIZIO  (
     ID_SERVIZIO int not null,
     NOME_SERVIZIO varchar(50) not null,
     RITORNO_NEGATIVI char(1) not null,
     RITORNO_STAMPE char(1) not null,
     PREZZO int not null,
		primary key (ID_SERVIZIO));

create table STATO_ORDINE (
     CODICE_STATO_ORDINE int not null,
     STATO  varchar(50) not null,
	primary key (CODICE_STATO_ORDINE));

create table UTENTE (
     ID_UTENTE int not null,
     NOME varchar(50) not null,
     COGNOME varchar(50) not null,
     TELEFONO varchar(50) not null,
     EMAIL varchar(50) not null,
     ATTIVO char(1) not null,
     NUM_ORDINI int not null,
     RUOLO varchar(50) not null,
     primary key (ID_UTENTE));

create table VALUTAZIONE_CRITERIO (
     ID_RECENSIONE int not null,
     ID_CRITERIO int not null,
     VOTO int not null,
     primary key (ID_RECENSIONE, ID_CRITERIO));
     
#Inserimenti

INSERT INTO RECENSIONE (ID_RECENSIONE, ID_ORDINE, DATA, COMMENTO) VALUES
(1, 17, '2025-05-21', 'Servizio impeccabile: la qualità delle scansioni ha superato le mie aspettative.'),
(2, 12, '2025-05-25', 'Consegna puntuale e ottima comunicazione durante tutto il processo.'),
(3, 33, '2025-05-21', 'Esperienza molto positiva: personale competente e disponibile.'),
(4, 15, '2025-05-24', 'La risoluzione delle immagini è eccellente, tornerò sicuramente.'),
(5, 4, '2025-05-23', 'Servizio rapido e professionale, consigliato a tutti gli appassionati.'),
(6, 50, '2025-05-21', 'Ottimo rapporto qualità-prezzo, soddisfatto del risultato finale.'),
(7, 32, '2025-05-25', 'Processo semplice e intuitivo, ideale anche per i meno esperti.'),
(8, 46, '2025-05-25', 'Risultati sorprendenti: le stampe sono di alta qualità.'),
(9, 16, '2025-05-25', 'Servizio clienti disponibile e pronto a risolvere ogni dubbio.'),
(10, 6, '2025-05-24', 'Esperienza complessiva molto soddisfacente, lo consiglio vivamente.'),
(11, 52, '2025-05-25', 'La qualità delle scansioni è eccezionale, dettagli nitidi e colori fedeli.'),
(12, 22, '2025-05-22', 'Consegna veloce e servizio clienti sempre disponibile e cordiale.'),
(13, 18, '2025-05-21', 'Esperienza positiva sotto ogni aspetto, tornerò sicuramente per altri sviluppi.'),
(14, 2, '2025-05-25', 'Servizio efficiente e risultati di alta qualità, molto soddisfatto.'),
(15, 8, '2025-05-25', 'Ottimo supporto durante tutto il processo, personale competente e gentile.'),
(16, 39, '2025-05-25', 'Le stampe sono arrivate in perfette condizioni, qualità eccellente.'),
(17, 24, '2025-05-25', 'Servizio rapido e preciso, ha superato le mie aspettative.'),
(18, 20, '2025-05-21', 'Esperienza molto positiva, consigliato per chi cerca qualità e professionalità.'),
(19, 37, '2025-05-25', 'Processo semplice e risultati eccellenti, tornerò sicuramente.'),
(20, 41, '2025-05-25', 'Servizio clienti disponibile e pronto a risolvere ogni problema.');
INSERT INTO PAGAMENTO (ID_PAGAMENTO, ID_ORDINE, METODO_DI_PAGAMENTO, VALUTA) VALUES
(1, 1, 'Carta di Credito', 'Euro'),
(2, 2, 'Carta di Credito', 'Euro'),
(3, 3, 'PayPal', 'Euro'),
(4, 4, 'Carta di Credito', 'Euro'),
(5, 5, 'Bonifico Bancario', 'Euro'),
(6, 6, 'Carta di Credito', 'Euro'),
(7, 7, 'Carta di Credito', 'Euro'),
(8, 8, 'PayPal', 'Euro'),
(9, 9, 'Carta di Credito', 'Euro'),
(10, 10, 'Carta di Credito', 'Euro'),
(11, 11, 'Bonifico Bancario', 'Euro'),
(12, 12, 'Carta di Credito', 'Euro'),
(13, 13, 'Carta di Credito', 'Euro'),
(14, 14, 'PayPal', 'Euro'),
(15, 15, 'Carta di Credito', 'Euro'),
(16, 16, 'Carta di Credito', 'Euro'),
(17, 17, 'Contrassegno', 'Euro'),
(18, 18, 'Carta di Credito', 'Euro'),
(19, 19, 'Carta di Credito', 'Euro'),
(20, 20, 'PayPal', 'Euro'),
(21, 21, 'Carta di Credito', 'Euro'),
(22, 22, 'Bonifico Bancario', 'Euro'),
(23, 23, 'Carta di Credito', 'Euro'),
(24, 24, 'Carta di Credito', 'Euro'),
(25, 25, 'PayPal', 'Euro'),
(26, 26, 'Carta di Credito', 'Euro'),
(27, 27, 'Carta di Credito', 'Euro'),
(28, 28, 'Contrassegno', 'Euro'),
(29, 29, 'Carta di Credito', 'Euro'),
(30, 30, 'Carta di Credito', 'Euro'),
(31, 31, 'PayPal', 'Euro'),
(32, 32, 'Carta di Credito', 'Euro'),
(33, 33, 'Bonifico Bancario', 'Euro'),
(34, 34, 'Carta di Credito', 'Euro'),
(35, 35, 'Carta di Credito', 'Euro'),
(36, 36, 'PayPal', 'Euro'),
(37, 37, 'Carta di Credito', 'Euro'),
(38, 38, 'Carta di Credito', 'Euro'),
(39, 39, 'Contrassegno', 'Euro'),
(40, 40, 'Carta di Credito', 'Euro'),
(41, 41, 'Carta di Credito', 'Euro'),
(42, 42, 'PayPal', 'Euro'),
(43, 43, 'Carta di Credito', 'Euro'),
(44, 44, 'Bonifico Bancario', 'Euro'),
(45, 45, 'Carta di Credito', 'Euro'),
(46, 46, 'Carta di Credito', 'Euro'),
(47, 47, 'PayPal', 'Euro'),
(48, 48, 'Carta di Credito', 'Euro'),
(49, 49, 'Carta di Credito', 'Euro'),
(50, 50, 'Contrassegno', 'Euro'),
(51, 51, 'Carta di Credito', 'Euro'),
(52, 52, 'Carta di Credito', 'Dollaro USA'), -- Varietà
(53, 53, 'PayPal', 'Euro'),
(54, 54, 'Carta di Credito', 'Euro'),
(55, 55, 'Bonifico Bancario', 'Sterlina Britannica'), -- Varietà
(56, 56, 'Carta di Credito', 'Euro'),
(57, 57, 'Carta di Credito', 'Euro'),
(58, 58, 'PayPal', 'Euro'),
(59, 59, 'Carta di Credito', 'Euro'),
(60, 60, 'Carta di Credito', 'Euro');

INSERT INTO STATO_ORDINE (CODICE_STATO_ORDINE, STATO) VALUES
(1, 'In lavorazione'),
(2, 'pronto per la spedizione'),
(3, 'in transito'),
(4, 'Completato');

INSERT INTO GENERAZIONE_STATO (ID_ORDINE, CODICE_STATO_ORDINE, DATA_INIZIO_STATO) VALUES
-- Ordine 1: In Stato 1
(1, 1, '2025-05-10'),

-- Ordine 2: Completo (Stato 1, 2, 3, 4)
(2, 1, '2025-05-11'),
(2, 2, '2025-05-12'),
(2, 3, '2025-05-15'),
(2, 4, '2025-05-19'),

-- Ordine 3: In Stato 2
(3, 1, '2025-05-12'),
(3, 2, '2025-05-14'),

-- Ordine 4: Completo (Stato 1, 2, 3, 4)
(4, 1, '2025-05-13'),
(4, 2, '2025-05-18'),
(4, 3, '2025-05-21'),
(4, 4, '2025-05-23'),

-- Ordine 5: In Stato 3
(5, 1, '2025-05-14'),
(5, 2, '2025-05-17'),
(5, 3, '2025-05-20'),

-- Ordine 6: Completo (Stato 1, 2, 3, 4)
(6, 1, '2025-05-15'),
(6, 2, '2025-05-20'),
(6, 3, '2025-05-22'),
(6, 4, '2025-05-24'),

-- Ordine 7: In Stato 1
(7, 1, '2025-05-16'),

-- Ordine 8: Completo (Stato 1, 2, 3, 4)
(8, 1, '2025-05-17'),
(8, 2, '2025-05-21'),
(8, 3, '2025-05-22'),
(8, 4, '2025-05-25'),

-- Ordine 9: In Stato 2
(9, 1, '2025-05-18'),
(9, 2, '2025-05-23'),

-- Ordine 10: Completo (Stato 1, 2, 3, 4)
(10, 1, '2025-05-19'),
(10, 2, '2025-05-21'),
(10, 3, '2025-05-23'),

-- Ordine 11: In Stato 3
(11, 1, '2025-05-20'),
(11, 2, '2025-05-22'),
(11, 3, '2025-05-25'),

-- Ordine 12: Completo (Stato 1, 2, 3, 4)
(12, 1, '2025-05-21'),
(12, 2, '2025-05-25'),


-- Ordine 13: In Stato 1
(13, 1, '2025-05-22'),

-- Ordine 14: Completo (Stato 1, 2, 3, 4)
(14, 1, '2025-05-23'),


-- Ordine 15: In Stato 2
(15, 1, '2025-05-24'),

-- Ordine 16: Completo (Stato 1, 2, 3, 4)
(16, 1, '2025-05-10'),
(16, 2, '2025-05-13'),
(16, 3, '2025-05-15'),
(16, 4, '2025-05-19'),

-- Ordine 17: In Stato 3
(17, 1, '2025-05-11'),
(17, 2, '2025-05-13'),
(17, 3, '2025-05-18'),
(17, 4, '2025-05-21'),

-- Ordine 18: Completo (Stato 1, 2, 3, 4)
(18, 1, '2025-05-12'),
(18, 2, '2025-05-13'),
(18, 3, '2025-05-16'),
(18, 4, '2025-05-21'),

-- Ordine 19: In Stato 1
(19, 1, '2025-05-13'),
(19, 2, '2025-05-14'),
(19, 3, '2025-05-17'),
(19, 4, '2025-05-21'),

-- Ordine 20: Completo (Stato 1, 2, 3, 4)
(20, 1, '2025-05-14'),
(20, 2, '2025-05-16'),
(20, 3, '2025-05-17'),
(20, 4, '2025-05-21'),

-- Ordine 21: In Stato 2
(21, 1, '2025-05-15'),
(21, 2, '2025-05-18'),
(21, 3, '2025-05-21'),
(21, 4, '2025-05-23'),

-- Ordine 22: Completo (Stato 1, 2, 3, 4)
(22, 1, '2025-05-16'),
(22, 2, '2025-05-17'),
(22, 3, '2025-05-20'),
(22, 4, '2025-05-22'),

-- Ordine 23: In Stato 3
(23, 1, '2025-05-17'),
(23, 2, '2025-05-20'),
(23, 3, '2025-05-22'),

-- Ordine 24: Completo (Stato 1, 2, 3, 4)
(24, 1, '2025-05-18'),
(24, 2, '2025-05-20'),
(24, 3, '2025-05-22'),
(24, 4, '2025-05-25'),

-- Ordine 25: In Stato 1
(25, 1, '2025-05-19'),
(25, 2, '2025-05-21'),
(25, 3, '2025-05-23'),

-- Ordine 26: Completo (Stato 1, 2, 3, 4)
(26, 1, '2025-05-20'),
(26, 2, '2025-05-22'),

-- Ordine 27: In Stato 2
(27, 1, '2025-05-21'),
(27, 2, '2025-05-22'),

-- Ordine 28: Completo (Stato 1, 2, 3, 4)
(28, 1, '2025-05-22'),
(28, 2, '2025-05-25'),


-- Ordine 29: In Stato 3
(29, 1, '2025-05-23'),

-- Ordine 30: Completo (Stato 1, 2, 3, 4)
(30, 1, '2025-05-24'),

-- Ordine 31: Completo (Stato 1, 2, 3, 4)
(31, 1, '2025-05-10'),
(31, 2, '2025-05-13'),
(31, 3, '2025-05-15'),
(31, 4, '2025-05-19'),

-- Ordine 32: In Stato 3
(32, 1, '2025-05-11'),
(32, 2, '2025-05-13'),
(32, 3, '2025-05-18'),
(32, 4, '2025-05-21'),

-- Ordine 33: Completo (Stato 1, 2, 3, 4)
(33, 1, '2025-05-12'),
(33, 2, '2025-05-13'),
(33, 3, '2025-05-16'),
(33, 4, '2025-05-21'),

-- Ordine 34: In Stato 1
(34, 1, '2025-05-13'),
(34, 2, '2025-05-14'),
(34, 3, '2025-05-17'),
(34, 4, '2025-05-21'),

-- Ordine 35: Completo (Stato 1, 2, 3, 4)
(35, 1, '2025-05-14'),
(35, 2, '2025-05-16'),
(35, 3, '2025-05-17'),
(35, 4, '2025-05-21'),

-- Ordine 36: In Stato 2
(36, 1, '2025-05-15'),
(36, 2, '2025-05-18'),
(36, 3, '2025-05-21'),
(36, 4, '2025-05-23'),

-- Ordine 37: Completo (Stato 1, 2, 3, 4)
(37, 1, '2025-05-16'),
(37, 2, '2025-05-17'),
(37, 3, '2025-05-20'),
(37, 4, '2025-05-22'),

-- Ordine 38: In Stato 3
(38, 1, '2025-05-17'),
(38, 2, '2025-05-20'),
(38, 3, '2025-05-22'),

-- Ordine 39: Completo (Stato 1, 2, 3, 4)
(39, 1, '2025-05-18'),
(39, 2, '2025-05-20'),
(39, 3, '2025-05-22'),
(39, 4, '2025-05-25'),

-- Ordine 40: In Stato 1
(40, 1, '2025-05-19'),
(40, 2, '2025-05-21'),
(40, 3, '2025-05-23'),

-- Ordine 41: Completo (Stato 1, 2, 3, 4)
(41, 1, '2025-05-20'),
(42, 2, '2025-05-22'),

-- Ordine 43: In Stato 2
(43, 1, '2025-05-21'),
(43, 2, '2025-05-22'),

-- Ordine 44: Completo (Stato 1, 2, 3, 4)
(44, 1, '2025-05-22'),
(44, 2, '2025-05-25'),


-- Ordine 45: In Stato 3
(45, 1, '2025-05-23'),

-- Ordine 46: Completo (Stato 1, 2, 3, 4)
(46, 1, '2025-05-10'),
(46, 2, '2025-05-13'),
(46, 3, '2025-05-15'),
(46, 4, '2025-05-19'),

-- Ordine 47: In Stato 3
(47, 1, '2025-05-11'),
(47, 2, '2025-05-13'),
(47, 3, '2025-05-18'),
(47, 4, '2025-05-21'),

-- Ordine 48: Completo (Stato 1, 2, 3, 4)
(48, 1, '2025-05-12'),
(48, 2, '2025-05-13'),
(48, 3, '2025-05-16'),
(48, 4, '2025-05-21'),

-- Ordine 49: In Stato 1
(49, 1, '2025-05-13'),
(49, 2, '2025-05-14'),
(49, 3, '2025-05-17'),
(49, 4, '2025-05-21'),

-- Ordine 50: Completo (Stato 1, 2, 3, 4)
(50, 1, '2025-05-14'),
(50, 2, '2025-05-16'),
(50, 3, '2025-05-17'),
(50, 4, '2025-05-21'),

-- Ordine 51: In Stato 2
(51, 1, '2025-05-15'),
(51, 2, '2025-05-18'),
(51, 3, '2025-05-21'),
(51, 4, '2025-05-23'),

-- Ordine 52: Completo (Stato 1, 2, 3, 4)
(52, 1, '2025-05-16'),
(52, 2, '2025-05-17'),
(52, 3, '2025-05-20'),
(52, 4, '2025-05-22'),

-- Ordine 53: In Stato 3
(53, 1, '2025-05-17'),
(53, 2, '2025-05-20'),
(53, 3, '2025-05-22'),

-- Ordine 54: Completo (Stato 1, 2, 3, 4)
(54, 1, '2025-05-18'),
(54, 2, '2025-05-20'),
(54, 3, '2025-05-22'),
(54, 4, '2025-05-25'),

-- Ordine 55: In Stato 1
(55, 1, '2025-05-19'),
(55, 2, '2025-05-21'),
(55, 3, '2025-05-23'),

-- Ordine 56: Completo (Stato 1, 2, 3, 4)
(56, 1, '2025-05-20'),
(56, 2, '2025-05-22'),

-- Ordine 57: In Stato 2
(57, 1, '2025-05-21'),
(57, 2, '2025-05-22'),

-- Ordine 58: Completo (Stato 1, 2, 3, 4)
(58, 1, '2025-05-22'),
(58, 2, '2025-05-25'),


-- Ordine 59: In Stato 3
(59, 1, '2025-05-23'),

-- Ordine 60: Completo (Stato 1, 2, 3, 4)
(60, 1, '2025-05-24');

INSERT INTO UTENTE
  (ID_UTENTE, NOME, COGNOME, TELEFONO, EMAIL, ATTIVO, NUM_ORDINI, RUOLO)
VALUES
  ( 1, 'Mario',     'Rossi',       '3456789001', 'mario.rossi1@example.com',   'Y', '3', 'user'),
  ( 2, 'Luca',      'Bianchi',     '3456789002', 'luca.bianchi2@example.com',  'Y', '1', 'user'),
  ( 3, 'Giulia',    'Verdi',       '3456789003', 'giulia.verdi3@example.com',  'Y', '2', 'user'),
  ( 4, 'Francesco', 'Neri',        '3456789004', 'francesco.neri4@example.com','Y', '0', 'admin'),
  ( 5, 'Anna',      'Santoro',     '3456789005', 'anna.santoro5@example.com',  'N', '0', 'user'),
  ( 6, 'Paolo',     'Ricci',       '3456789006', 'paolo.ricci6@example.com',   'Y', '4', 'user'),
  ( 7, 'Elena',     'Fontana',     '3456789007', 'elena.fontana7@example.com', 'Y', '2', 'user'),
  ( 8, 'Stefano',   'Sala',        '3456789008', 'stefano.sala8@example.com',  'N', '0', 'user'),
  ( 9, 'Martina',   'Galli',       '3456789009', 'martina.galli9@example.com', 'Y', '3', 'user'),
  (10, 'Davide',    'Bianco',      '3456789010', 'davide.bianco10@example.com','Y', '1', 'user'),
  (11, 'Federica',  'Greco',       '3456789011', 'federica.greco11@example.com','Y', '2', 'user'),
  (12, 'Alessandro','Marini',      '3456789012', 'alessandro.marini12@example.com','Y','0','admin'),
  (13, 'Chiara',    'Ferrari',     '3456789013', 'chiara.ferrari13@example.com','Y','2','user'),
  (14, 'Simone',    'Costa',       '3456789014', 'simone.costa14@example.com', 'Y','4','user'),
  (15, 'Laura',     'De Luca',     '3456789015', 'laura.deluca15@example.com',  'N','0','user'),
  (16, 'Roberto',   'Guerra',      '3456789016', 'roberto.guerra16@example.com','Y','3','user'),
  (17, 'Sonia',     'Barbieri',    '3456789017', 'sonia.barbieri17@example.com','Y','1','user'),
  (18, 'Matteo',    'Parisi',      '3456789018', 'matteo.parisi18@example.com','Y','5','user'),
  (19, 'Sara',      'Conti',       '3456789019', 'sara.conti19@example.com',   'Y','2','user'),
  (20, 'Giorgio',   'Rinaldi',     '3456789020', 'giorgio.rinaldi20@example.com','N','0','user'),
  (21, 'Rebecca',   'Martelli',    '3456789021', 'rebecca.martelli21@example.com','Y','3','user'),
  (22, 'Marco',     'Meloni',      '3456789022', 'marco.meloni22@example.com', 'Y','2','user'),
  (23, 'Valentina', 'Serra',       '3456789023', 'valentina.serra23@example.com','Y','4','user'),
  (24, 'Riccardo',  'Fonti',       '3456789024', 'riccardo.fonti24@example.com','Y','1','user'),
  (25, 'Elisa',     'Moretti',     '3456789025', 'elisa.moretti25@example.com', 'N','0','user'),
  (26, 'Andrea',    'Sanna',       '3456789026', 'andrea.sanna26@example.com', 'Y','3','user'),
  (27, 'Claudia',   'Monti',       '3456789027', 'claudia.monti27@example.com','Y','2','user'),
  (28, 'Nicola',    'Pellegrini',  '3456789028', 'nicola.pellegrini28@example.com','Y','5','user'),
  (29, 'Daniela',   'Ferrara',     '3456789029', 'daniela.ferrara29@example.com','N','0','user'),
  (30, 'Emanuele',  'Grassi',      '3456789030', 'emanuele.grassi30@example.com','Y','1','user');


INSERT INTO CATEGORIA (ID_CATEGORIA, NOME) VALUES
  ('01', 'macchine fotografiche'),
  ('02', 'rullini'),
  ('03', 'accessori'),
  ('04', 'obiettivi');
  
INSERT INTO SERVIZIO (ID_SERVIZIO, NOME_SERVIZIO, RITORNO_NEGATIVI, RITORNO_STAMPE, PREZZO) VALUES
(1, 'Solo Scansione', 'N', 'N', 12),
(2, 'Scansione + Negativi', 'Y', 'N', 18),
(3, 'Scansione + Negativi + Stampe', 'Y', 'Y', 22);


INSERT INTO CRITERIO_DA_VALUTARE (ID_CRITERIO, NOME) VALUES
  ('01', 'Qualità prodotto'),
  ('02', 'Tempi di consegna'),
  ('03', 'Condizioni imballaggio'),
  ('04', 'Prezzo'),
  ('05', 'Servizio clienti');

INSERT INTO INDIRIZZI_DI_SPEDIZIONE (ID_INDIRIZZO, VIA, CITTA, CAP, PROVINCIA, REGIONE, ID_UTENTE) VALUES
(1, 'Via del Mare 104', 'Bellaria', '43298', 'BE', 'Piemonte', 16),
(2, 'Via del Sole 180', 'Dorsalia', '98544', 'DS', 'Veneto', 1),
(3, 'Via del Sole 37', 'Etruria', '76622', 'ET', 'Trentino', 11),
(4, 'Via delle Magnolie 136', 'Fossanova', '66455', 'FO', 'Friuli', 14),
(5, 'Via del Sole 12', 'Gallarate', '62457', 'GA', 'Liguria', 4),
(6, 'Via Aldo Moro 111', 'Montevico', '43658', 'MV', 'Lazio', 4),
(7, 'Via dei Platani 111', 'Nemoralia', '93959', 'NM', 'Abruzzo', 19),
(8, 'Corso Italia 23', 'Orsara', '83947', 'OR', 'Molise', 30),
(9, 'Via del Sole 179', 'Pietravalle', '91824', 'PV', 'Basilicata', 9),
(10, 'Via delle Magnolie 147', 'Quantoria', '78085', 'QT', 'Calabria', 29),
(11, 'Via delle Ginestre 15', 'Artemisia', '78698', 'AR', 'Val d''Aosta', 28),
(12, 'Via dei Pini 47', 'Dorsalia', '18528', 'DS', 'Veneto', 11),
(13, 'Via delle Magnolie 22', 'Fossanova', '40642', 'FO', 'Friuli', 11),
(14, 'Corso Italia 10', 'Gallarate', '63478', 'GA', 'Liguria', 29),
(15, 'Via Aldo Moro 132', 'Italica', '91208', 'IT', 'Toscana', 19),
(16, 'Via delle Magnolie 15', 'Luminara', '24299', 'LU', 'Umbria', 1),
(17, 'Via delle Ginestre 13', 'Orsara', '34183', 'OR', 'Molise', 23),
(18, 'Viale Europa 85', 'Pietravalle', '57017', 'PV', 'Basilicata', 14),
(19, 'Piazza Libertà 16', 'Artemisia', '13736', 'AR', 'Val d''Aosta', 16),
(20, 'Via Nuova 191', 'Bellaria', '69244', 'BE', 'Piemonte', 25),
(21, 'Via dei Gelsi 73', 'Castelverde', '87789', 'CV', 'Lombardia', 28),
(22, 'Via delle Ginestre 61', 'Dorsalia', '86162', 'DS', 'Veneto', 11),
(23, 'Via dei Platani 23', 'Etruria', '98528', 'ET', 'Trentino', 16),
(24, 'Via del Sole 134', 'Gallarate', '71491', 'GA', 'Liguria', 3),
(25, 'Via delle Rose 4', 'Italica', '41225', 'IT', 'Toscana', 24),
(26, 'Via dei Platani 99', 'Luminara', '29784', 'LU', 'Umbria', 18),
(27, 'Via dei Pini 157', 'Montevico', '77916', 'MV', 'Lazio', 20),
(28, 'Via delle Rose 88', 'Orsara', '49591', 'OR', 'Molise', 9),
(29, 'Via dei Tigli 137', 'Pietravalle', '79483', 'PV', 'Basilicata', 30),
(30, 'Via dei Pini 48', 'Quantoria', '92037', 'QT', 'Calabria', 1),
(31, 'Via del Mare 49', 'Fossanova', '60572', 'FO', 'Friuli', 18),
(32, 'Via dei Platani 165', 'Gallarate', '59852', 'GA', 'Liguria', 12),
(33, 'Via San Giovanni 21', 'Nemoralia', '61899', 'NM', 'Abruzzo', 10),
(34, 'Via delle Ginestre 45', 'Orsara', '21902', 'OR', 'Molise', 22),
(35, 'Via delle Magnolie 40', 'Pietravalle', '20230', 'PV', 'Basilicata', 22),
(36, 'Corso Italia 84', 'Artemisia', '90224', 'AR', 'Val d''Aosta', 7),
(37, 'Via Nuova 47', 'Solaria', '50654', 'SO', 'Lombardia', 25),
(38, 'Via Nuova 61', 'Tranquillia', '50536', 'TR', 'Sicilia', 7),
(39, 'Via Nuova 81', 'Floridia', '50871', 'FL', 'Puglia', 14),
(40, 'Via Nuova 99', 'Belladonna', '50724', 'BD', 'Campania', 18),
(41, 'Via Nuova 177', 'Ventosa', '50782', 'VE', 'Emilia-Romagna', 20),
(42, 'Via Nuova 152', 'Miralago', '50947', 'MI', 'Sardegna', 5),
(43, 'Via Nuova 110', 'Verdevia', '50419', 'VV', 'Marche', 12),
(44, 'Via dei Fiori 21', 'Borgonuovo', '50123', 'BG', 'Lombardia', 2),
(45, 'Via delle Acacie 8', 'Lunaria', '60214', 'LU', 'Toscana', 6),
(46, 'Via Roma 99', 'Stellata', '71523', 'ST', 'Lazio', 8),
(47, 'Piazza dei Tigli 3', 'Aurora', '81245', 'AU', 'Piemonte', 13),
(48, 'Via delle Rose 12', 'Nevaria', '92351', 'NE', 'Veneto', 15),
(49, 'Via dei Pini 74', 'Serenella', '73125', 'SE', 'Emilia-Romagna', 17),
(50, 'Via Montagna 51', 'Vallechiara', '84562', 'VC', 'Abruzzo', 21),
(51, 'Via delle Orchidee 6', 'Bosconero', '90217', 'BO', 'Marche', 26),
(52, 'Corso Nuovo 44', 'Tranquillia', '51984', 'TR', 'Sicilia', 27);

INSERT INTO RULLINO (ID_RULLINO, NUMERO_SCATTI, RISOLUZIONE, ID_SERVIZIO, ID_ORDINE) VALUES
(1, 24, 'ultra', 3, 1),
(2, 24, 'ultra', 3, 3),
(3, 24, 'basic', 2, 5),
(4, 36, 'medium', 3, 5),
(5, 24, 'medium', 1, 7),
(6, 36, 'medium', 2, 9),
(7, 36, 'ultra', 2, 11),
(8, 36, 'basic', 2, 11),
(9, 36, 'medium', 2, 13),
(10, 24, 'medium', 2, 15),
(11, 24, 'medium', 2, 17),
(12, 24, 'ultra', 3, 17),
(13, 36, 'ultra', 1, 19),
(14, 24, 'ultra', 1, 21),
(15, 24, 'ultra', 1, 21),
(16, 36, 'ultra', 3, 23),
(17, 36, 'medium', 1, 23),
(18, 24, 'basic', 1, 25),
(19, 24, 'basic', 2, 25),
(20, 24, 'basic', 1, 27),
(21, 24, 'basic', 3, 29),
(22, 24, 'ultra', 3, 31),
(23, 24, 'basic', 2, 33),
(24, 24, 'basic', 3, 35),
(25, 24, 'ultra', 3, 35),
(26, 36, 'medium', 3, 37),
(27, 24, 'medium', 1, 37),
(28, 36, 'medium', 1, 39),
(29, 24, 'basic', 2, 41),
(30, 24, 'basic', 3, 43),
(31, 36, 'ultra', 3, 43),
(32, 24, 'basic', 1, 45),
(33, 36, 'basic', 3, 45),
(34, 36, 'ultra', 3, 47),
(35, 24, 'medium', 3, 47),
(36, 24, 'basic', 1, 49),
(37, 36, 'medium', 3, 49),
(38, 24, 'medium', 2, 51),
(39, 36, 'ultra', 3, 53),
(40, 24, 'ultra', 3, 53),
(41, 36, 'basic', 3, 55),
(42, 24, 'medium', 1, 57),
(43, 36, 'basic', 3, 59),
(44, 24, 'ultra', 1, 59);

INSERT INTO LABORATORIO (ID_LABORATORIO, NOME, CITTA, INDIRIZZO, REGIONE, PROVINCIA, CAP) VALUES
(1, 'Studio Fahrenheit', 'Roma', 'Via delle Ombre 12', 'Lazio', 'RM', '00185'),
(2, 'Yes We Scan', 'Milano', 'Viale della Fotografia 45', 'Lombardia', 'MI', '20126'),
(3, 'Ars-Imago', 'Torino', 'Corso Analogico 32', 'Piemonte', 'TO', '10121');

INSERT INTO ORDINE (ID_ORDINE, ID_PAGAMENTO, DATA_ORDINE, TIPO_ORDINE, IMPORTO, ID_LABORATORIO, ID_INDIRIZZO, ID_UTENTE) VALUES
(1, 1, '2025-05-10', 'sviluppo', 22.00, null, 1, 1),
(2, 2, '2025-05-11', 'prodotto', 1079.00, 2, 2, 2),
(3, 3, '2025-05-12', 'sviluppo', 22.00, null, 3, 3),
(4, 4, '2025-05-13', 'prodotto', 1798.00, 1, 4, 4),
(5, 5, '2025-05-14', 'sviluppo', 40.00, null, 5, 5),
(6, 6, '2025-05-15', 'prodotto', 13.98, 3, 6, 6),
(7, 7, '2025-05-16', 'sviluppo', 12.00, null, 7, 7),
(8, 8, '2025-05-17', 'prodotto', 39.99, 2, 8, 8),
(9, 9, '2025-05-18', 'sviluppo', 18.00, null, 9, 9),
(10, 10, '2025-05-19', 'prodotto', 2897.00, 1, 10, 10),
(11, 11, '2025-05-20', 'sviluppo', 36.00, null, 11, 11),
(12, 12, '2025-05-21', 'prodotto', 16.50, 3, 12, 12),
(13, 13, '2025-05-22', 'sviluppo', 18.00, null, 13, 13),
(14, 14, '2025-05-23', 'prodotto', 170.00, 2, 14, 14),
(15, 15, '2025-05-24', 'sviluppo', 18.00, null, 15, 15),
(16, 16, '2025-05-10', 'prodotto', 95.00, 1, 16, 16),
(17, 17, '2025-05-11', 'sviluppo', 40.00, null, 17, 17),
(18, 18, '2025-05-12', 'prodotto', 11.50, 3, 18, 18),
(19, 19, '2025-05-13', 'sviluppo', 12.00, null, 19, 19),
(20, 20, '2025-05-14', 'prodotto', 398.00, 2, 20, 20),
(21, 21, '2025-05-15', 'sviluppo', 24.00, null, 21, 21),
(22, 22, '2025-05-16', 'prodotto', 1147.99, 1, 22, 22),
(23, 23, '2025-05-17', 'sviluppo', 34.00, null, 23, 23),
(24, 24, '2025-05-18', 'prodotto', 31.00, 3, 24, 24),
(25, 25, '2025-05-19', 'sviluppo', 30.00, null, 25, 25),
(26, 26, '2025-05-20', 'prodotto', 850.00, 2, 26, 26),
(27, 27, '2025-05-21', 'sviluppo', 12.00, null, 27, 27),
(28, 28, '2025-05-22', 'prodotto', 1069.98, 1, 28, 28),
(29, 29, '2025-05-23', 'sviluppo', 22.00, null, 29, 29),
(30, 30, '2025-05-24', 'prodotto', 749.00, 3, 30, 30),
(31, 31, '2025-05-10', 'sviluppo', 22.00, null, 31, 23),
(32, 32, '2025-05-11', 'prodotto', 29.98, 2, 32, 3),
(33, 33, '2025-05-12', 'sviluppo', 18.00, null, 33, 14),
(34, 34, '2025-05-13', 'prodotto', 204.99, 1, 34, 21),
(35, 35, '2025-05-14', 'sviluppo', 44.00, null, 35, 29),
(36, 36, '2025-05-15', 'prodotto', 399.00, 3, 36, 23),
(37, 37, '2025-05-16', 'sviluppo', 34.00, null, 37, 2),
(38, 38, '2025-05-17', 'prodotto', 138.00, 2, 38, 23),
(39, 39, '2025-05-18', 'sviluppo', 12.00, null, 39, 25),
(40, 40, '2025-05-19', 'prodotto', 19.90, 1, 40, 10),
(41, 41, '2025-05-20', 'sviluppo', 18.00, null, 41, 21),
(42, 42, '2025-05-21', 'prodotto', 379.00, 3, 42, 4),
(43, 43, '2025-05-22', 'sviluppo', 44.00, null, 43, 5),
(44, 44, '2025-05-23', 'prodotto', 13.99, 2, 44, 12),
(45, 45, '2025-05-24', 'sviluppo', 34.00, null, 45, 11),
(46, 46, '2025-05-10', 'prodotto', 1249.00, 1, 46, 13),
(47, 47, '2025-05-11', 'sviluppo', 44.00, null, 47, 17),
(48, 48, '2025-05-12', 'prodotto', 1499.98, 3, 48, 18),
(49, 49, '2025-05-13', 'sviluppo', 34.00, null, 49, 29),
(50, 50, '2025-05-14', 'prodotto', 378.00, 2, 50, 30),
(51, 51, '2025-05-15', 'sviluppo', 18.00, null, 51, 1),
(52, 52, '2025-05-16', 'prodotto', 21.98, 1, 52, 2),
(53, 53, '2025-05-17', 'sviluppo', 44.00, null, 34, 3),
(54, 54, '2025-05-18', 'prodotto', 199.00, 3, 21, 4),
(55, 55, '2025-05-19', 'sviluppo', 22.00, null, 2, 5),
(56, 56, '2025-05-20', 'prodotto', 139.00, 2, 17, 6),
(57, 57, '2025-05-21', 'sviluppo', 12.00, null, 37, 7),
(58, 58, '2025-05-22', 'prodotto', 29.90, 1, 48, 8),
(59, 59, '2025-05-23', 'sviluppo', 34.00, null, 9, 9),
(60, 60, '2025-05-24', 'prodotto', 195.00, 3, 16, 10);



INSERT INTO PRODOTTO (ID_PRODOTTO, NOME, MARCA, MODELLO, PREZZO, DESCRIZIONE, ID_CATEGORIA) VALUES
(1, 'Reflex EOS 2000D', 'Canon', '2000D', '499.99', 'Fotocamera reflex entry-level', 1),
(2, 'Alpha 7 III', 'Sony', 'ILCE-7M3', '1999.00', 'Mirrorless full-frame', 1),
(3, 'Z50', 'Nikon', 'Z50', '999.00', 'Mirrorless compatta APS-C', 1),
(4, 'Lumix G100', 'Panasonic', 'G100', '749.99', 'Ideale per vlogging', 1),
(5, 'X-T30 II', 'Fujifilm', 'X-T30 II', '899.00', 'Retro design e qualità d\'immagine', 1),
(6, 'OM-D E-M10', 'Olympus', 'E-M10 Mark IV', '699.00', 'Fotocamera micro 4/3', 1),
(7, 'EOS R50', 'Canon', 'R50', '850.00', 'Mirrorless compatta', 1),
(8, 'Alpha 6400', 'Sony', 'ILCE-6400', '1150.00', 'Compatta con autofocus rapido', 1),
(9, 'Coolpix B600', 'Nikon', 'B600', '399.00', 'Bridge con super zoom', 1),
(10, 'X-S10', 'Fujifilm', 'X-S10', '1049.00', 'Stabilizzazione a 5 assi', 1),
(11, 'Kodak Color Plus 200', 'Kodak', 'Color200', '9.99', 'Rullino colore 35mm', 2),
(12, 'Fujifilm Superia X-TRA 400', 'Fujifilm', 'XTRA400', '11.50', 'Pellicola colore versatile', 2),
(13, 'Ilford HP5 Plus 400', 'Ilford', 'HP5', '8.99', 'Pellicola bianco e nero', 2),
(14, 'Kodak Portra 400', 'Kodak', 'Portra400', '14.99', 'Alta qualità colore', 2),
(15, 'Foma Fomapan 100', 'Foma', 'F100', '6.99', 'B/N classico e nitido', 2),
(16, 'Cinestill 800T', 'Cinestill', '800T', '13.99', 'Tungsteno per luce artificiale', 2),
(17, 'Lomography Color Negative 400', 'Lomography', 'CN400', '12.00', 'Colori saturi', 2),
(18, 'Kodak Ektar 100', 'Kodak', 'Ektar100', '15.50', 'Alta saturazione colore', 2),
(19, 'Ilford Delta 3200', 'Ilford', 'Delta3200', '10.99', 'Alta sensibilità B/N', 2),
(20, 'Revolog Tesla 2', 'Revolog', 'Tesla2', '16.50', 'Effetti creativi elettrici', 2),
(21, 'Treppiede Compact Action', 'Manfrotto', 'MKCOMPACT', '69.99', 'Treppiede da viaggio', 3),
(22, 'Zaino Camera Bag', 'Lowepro', 'Tahoe BP150', '59.90', 'Zaino per reflex e obiettivi', 3),
(23, 'Scheda SD 64GB', 'SanDisk', 'Extreme Pro', '29.90', 'Velocità UHS-I 170MB/s', 3),
(24, 'Pulizia sensore', 'VSGO', 'DDR-16', '19.90', 'Kit pulizia sensori', 3),
(25, 'Filtro UV 58mm', 'Hoya', 'UV58', '15.00', 'Protezione obiettivo', 3),
(26, 'Flash Speedlite 430EX III', 'Canon', '430EX III', '249.00', 'Flash esterno TTL', 3),
(27, 'Batteria NP-FZ100', 'Sony', 'NP-FZ100', '75.00', 'Batteria per Alpha serie 7', 3),
(28, 'Grip per Z6/Z7', 'Nikon', 'MB-N10', '199.00', 'Impugnatura con batteria', 3),
(29, 'Cinghia in pelle', 'Peak Design', 'Slide Lite', '49.99', 'Cinghia regolabile', 3),
(30, 'Filtro ND variabile', 'K&F Concept', 'ND2-400', '39.99', 'Per esposizione lunga', 3),
(31, 'obiettivo EF 50mm f/1.8 STM', 'Canon', 'EF50STM', '125.00', 'Obiettivo fisso luminoso', 4),
(32, 'obiettivo 35mm f/1.8 DX', 'Nikon', 'AF-S DX 35mm', '195.00', 'Focale fissa APS-C', 4),
(33, 'obiettivo 18-55mm IS II', 'Canon', 'EF-S 18-55', '85.00', 'Zoom standard', 4),
(34, 'obiettivo 16-50mm OSS', 'Sony', 'E PZ 16-50', '145.00', 'Compatto per mirrorless', 4),
(35, 'obiettivo XC 15-45mm', 'Fujifilm', 'XC15-45', '170.00', 'Zoom grandangolare', 4),
(36, 'obiettivo 75-300mm f/4-5.6 III', 'Canon', 'EF 75-300', '199.00', 'Teleobiettivo economico', 4),
(37, 'obiettivo 70-200mm f/2.8', 'Nikon', 'AF-S 70-200', '2199.00', 'Tele professionale', 4),
(38, 'obiettivo 100-400mm GM OSS', 'Sony', 'FE 100-400', '2599.00', 'Super teleobiettivo', 4),
(39, 'obiettivo 24-105mm f/4 G OSS', 'Sony', 'FE 24-105', '1249.00', 'Zoom versatile', 4),
(40, 'obiettivo XF 56mm f/1.2 R', 'Fujifilm', 'XF56', '899.00', 'Ritratto luminoso', 4),
(41, 'Instax Mini 90', 'Fujifilm', 'Mini 90', '129.00', 'Fotocamera istantanea', 1),
(42, 'Polaroid Now+', 'Polaroid', 'Now+', '149.00', 'Istantanea con Bluetooth', 1),
(43, 'AgfaPhoto Vista Plus 200', 'AgfaPhoto', 'Vista200', '7.50', 'Colori caldi 35mm', 2),
(44, 'Lomography Redscale XR', 'Lomography', 'RedscaleXR', '10.50', 'Effetto rosso vintage', 2),
(45, 'Luce LED portatile', 'Aputure', 'MC RGB', '95.00', 'Luce LED regolabile', 3),
(46, 'Microfono direzionale', 'Rode', 'VideoMic GO', '69.00', 'Audio per video', 3),
(47, 'obiettivo 24mm f/2.8 STM', 'Canon', 'EF-S 24mm', '139.00', 'Grandangolo compatto', 4),
(48, 'obiettivo 85mm f/1.8 G', 'Nikon', 'AF-S 85mm', '449.00', 'Ritratto nitido', 4),
(49, 'obiettivo 35mm f/1.4', 'Sigma', 'Art 35mm DG HSM', '749.00', 'Alta qualità per FF', 4),
(50, 'obiettivo XF 23mm f/2 WR', 'Fujifilm', 'XF23mmF2', '429.00', 'Grandangolo tropicalizzato', 4);  

INSERT INTO INCLUDE (ID_ORDINE, ID_PRODOTTO, QUANTITA) VALUES
(2, 10, 1),
(2, 25, 2),
(4, 5, 1),
(4, 40, 1),
(6, 15, 2),
(8, 30, 1),
(10, 2, 1),
(10, 48, 2),
(12, 20, 1),
(14, 35, 1),
(14, 8, 2),
(16, 45, 1),
(18, 12, 1),
(20, 28, 2),
(22, 3, 1),
(22, 42, 1),
(24, 18, 2),
(26, 7, 1),
(28, 33, 1),
(28, 1, 2),
(30, 49, 1),
(32, 14, 2),
(34, 21, 1),
(34, 38, 1),
(36, 9, 1),
(38, 46, 2),
(40, 24, 1),
(42, 11, 1),
(42, 31, 2),
(44, 16, 1),
(46, 39, 1),
(48, 4, 2),
(50, 26, 1),
(50, 41, 1),
(52, 19, 2),
(54, 36, 1),
(56, 13, 1),
(56, 47, 2),
(58, 23, 1),
(60, 32, 1);
INSERT INTO VALUTAZIONE_CRITERIO (ID_RECENSIONE, ID_CRITERIO, VOTO) VALUES
(1, 1, 4), (1, 2, 5), (1, 3, 3), (1, 4, 4), (1, 5, 2),
(2, 1, 3), (2, 2, 4), (2, 3, 5), (2, 4, 3), (2, 5, 4),
(3, 1, 5), (3, 2, 3), (3, 3, 4), (3, 4, 5), (3, 5, 3),
(4, 1, 2), (4, 2, 5), (4, 3, 3), (4, 4, 4), (4, 5, 5),
(5, 1, 4), (5, 2, 2), (5, 3, 5), (5, 4, 3), (5, 5, 4),
(6, 1, 3), (6, 2, 4), (6, 3, 2), (6, 4, 5), (6, 5, 3),
(7, 1, 5), (7, 2, 3), (7, 3, 4), (7, 4, 2), (7, 5, 5),
(8, 1, 4), (8, 2, 5), (8, 3, 3), (8, 4, 4), (8, 5, 3),
(9, 1, 3), (9, 2, 2), (9, 3, 5), (9, 4, 3), (9, 5, 4),
(10, 1, 5), (10, 2, 4), (10, 3, 2), (10, 4, 5), (10, 5, 3),
(11, 1, 2), (11, 2, 3), (11, 3, 4), (11, 4, 2), (11, 5, 5),
(12, 1, 4), (12, 2, 5), (12, 3, 3), (12, 4, 4), (12, 5, 3),
(13, 1, 3), (13, 2, 2), (13, 3, 5), (13, 4, 3), (13, 5, 4),
(14, 1, 5), (14, 2, 4), (14, 3, 2), (14, 4, 5), (14, 5, 3),
(15, 1, 4), (15, 2, 3), (15, 3, 4), (15, 4, 2), (15, 5, 5),
(16, 1, 3), (16, 2, 5), (16, 3, 3), (16, 4, 4), (16, 5, 2),
(17, 1, 5), (17, 2, 2), (17, 3, 5), (17, 4, 3), (17, 5, 4),
(18, 1, 4), (18, 2, 4), (18, 3, 2), (18, 4, 5), (18, 5, 3),
(19, 1, 3), (19, 2, 3), (19, 3, 4), (19, 4, 2), (19, 5, 5),
(20, 1, 5), (20, 2, 5), (20, 3, 3), (20, 4, 4), (20, 5, 3);




-- Constraints Section
-- ___________________ 

ALTER TABLE VALUTAZIONE_CRITERIO
  ADD CONSTRAINT FK_VAL_CRIT_CRIT
    FOREIGN KEY (ID_CRITERIO)
    REFERENCES CRITERIO_DA_VALUTARE(ID_CRITERIO)
    ON DELETE RESTRICT
    ON UPDATE CASCADE;

ALTER TABLE GENERAZIONE_STATO
  ADD CONSTRAINT FK_GENSTATO_ORDINE
    FOREIGN KEY (ID_ORDINE)
    REFERENCES ORDINE(ID_ORDINE)
    ON DELETE RESTRICT
    ON UPDATE CASCADE;


alter table INCLUDE add constraint FKINC_ORD
     foreign key (ID_ORDINE)
     references ORDINE (ID_ORDINE);

alter table INCLUDE add constraint FKINC_PRO
     foreign key (ID_PRODOTTO)
     references PRODOTTO (ID_PRODOTTO);

alter table INDIRIZZI_DI_SPEDIZIONE add constraint FKPOSSIEDE
     foreign key (ID_UTENTE)
     references UTENTE (ID_UTENTE);
     
alter table ORDINE add constraint FKR
     foreign key (ID_LABORATORIO)
     references LABORATORIO (ID_LABORATORIO);

alter table ORDINE add constraint FKCONSEGNA_A 
     foreign key (ID_INDIRIZZO)
     references INDIRIZZI_DI_SPEDIZIONE (ID_INDIRIZZO);

alter table ORDINE add constraint FKCORRISPONDE_FK
     foreign key (ID_PAGAMENTO)
     references PAGAMENTO (ID_PAGAMENTO);
     
ALTER TABLE ORDINE
  ADD CONSTRAINT FK_ORDINE_UTENTE
    FOREIGN KEY (ID_UTENTE)
    REFERENCES UTENTE(ID_UTENTE)
    ON DELETE RESTRICT
    ON UPDATE CASCADE;


ALTER TABLE PRODOTTO
  ADD CONSTRAINT FK_PRODOTTO_CATEGORIA
    FOREIGN KEY (ID_CATEGORIA)
    REFERENCES CATEGORIA(ID_CATEGORIA)
    ON DELETE RESTRICT
    ON UPDATE CASCADE;
    
ALTER TABLE VALUTAZIONE_CRITERIO
  ADD CONSTRAINT FK_VALCRIT_RECENSIONE
    FOREIGN KEY (ID_RECENSIONE)
    REFERENCES RECENSIONE(ID_RECENSIONE)
    ON DELETE RESTRICT
    ON UPDATE CASCADE;


alter table RECENSIONE add constraint FKRIGUARDA_FK
     foreign key (ID_ORDINE)
     references ORDINE (ID_ORDINE);

alter table RULLINO add constraint FKR_RICHIEDE
     foreign key (ID_SERVIZIO)
     references SERVIZIO  (ID_SERVIZIO);

alter table RULLINO add constraint FKCONTIENE
     foreign key (ID_ORDINE)
     references ORDINE (ID_ORDINE);
ALTER TABLE PAGAMENTO ADD CONSTRAINT FK_PAGAMENTO_ORDINE
    FOREIGN KEY (ID_ORDINE)
    REFERENCES ORDINE(ID_ORDINE);

ALTER TABLE GENERAZIONE_STATO
  ADD CONSTRAINT FK_GENSTATO_STATOORDINE
    FOREIGN KEY (CODICE_STATO_ORDINE)
    REFERENCES STATO_ORDINE(CODICE_STATO_ORDINE)
    ON DELETE RESTRICT
    ON UPDATE CASCADE;

alter table VALUTAZIONE_CRITERIO add constraint FKVAL_CRI
     foreign key (ID_CRITERIO)
     references CRITERIO_DA_VALUTARE (ID_CRITERIO);
	
-- Index Section
-- _____________ 


# B2B Market
Contributors: MarketPress
Requires at least: 4.9+
Tested up to: 4.9+
Stable tag: 1.0.2

## Description

WooCommerce und B2B-Shops passen endlich zusammen, erstmals auch im deutschsprachigen Bereich. Verkaufe gleichzeitig an B2B und B2C. Mit individuellen Preisen für unterschiedliche Kunden, Prüfung der USt-ID, Staffelpreisen, erweiterten Rabatten und vielem mehr. Erweitere deine Umsätze auf Geschäftskunden, Endkunden und weitere Zielgruppen - mit B2B Market.

### Features
<https://marketpress.de/shop/plugins/b2b-market/>

## Installation

### Requirements
* WordPress 4.9+*
* PHP 5.6+*
* WooCommerce 3.4+*

# Installation
 * Installieren Sie zuerst WooCommerce
 * Installieren Sie die Standardseiten für WooCommerce (Folgen Sie dazu der Installationsroutine von WooCommerce)
 * Benutzen Sie den installer im Backend, oder

1. Entpacken sie das zip-Archiv
2. Laden sie es in das `/wp-content/plugins/` Verzeichnis ihrer WordPress Installation auf ihrem Webserver
3. Aktivieren Sie das Plugin über das 'Plugins' Menü in WordPress und drücken Sie aktivieren
4. Folgen Sie den Anweisungen des Installationsbildschirms

## Other Notes
# Acknowledgements
Thanks Mike Jolley (http://mikejolley.com/) for supporting us with the WooCommerce core.

# Licence
 GPL Version 3

# Languages
- English (en_US) (default)
- German (de_DE)

## Changelog

= 1.0 =
- Release

= 1.0.1 =
- Staffelpreise je Variante
- Neues Admin-Interface für Staffelpreise in Produkten
- Angepasste Live-Preis-Berechnung für Varianten
- Korrigierte Übersetzungen
- Behandlung von Umlauten und Sonderzeichen in Kundengruppen-Namen
- Steuer-Darstellung bei Netto-Preisen
- Komma-Preise in allen Feldern
- Konditionale Überprüfung für Warenkorb-Rabatte
- Produktauswahl bei langen Listen
- Fallback-Lösung bei riesigem Produktbestand in Select2
- Minifizierung Scripts und Styles
- REST API Bug in Zusammenhang mit Kalkulation

= 1.0.2 =
- Addon: Min-und Max-Mengen je Produkt und Kundengruppe
- Erweiterung und Bugfixes für Migrator (Varianten)
- Bugfixes Windows-Server und Migrator
- Umfassende Performance-Optimierung und neue Einstellung
- Kunden-und Gastgruppe nutz-und importierbar für Anwendung von Regeln für Gäste und normale Kunden
- Option zur Deaktivierung der Whitelist-Funktion für bestimmte Themes
- Shortcode zur konditionalen Ausgabe nach Kundengruppe (z.B. AGB)
- Neues Staffelpreis-Interface zur besseren Eingabe und eine effizientere Speicherung
- Umbenennung von Kundengruppen / Löschen von Kundengruppen / Anpassung von Kundengruppen verbessert
- Support für Staffel-und Gruppenpreise für Ajax- und Mini-Carts
- Bugfixes: Rundungsfehler in Live-Preis-Berechnung
- Bugfixes: Netto / Brutto-Preis-Berechnung
- Versandmethoden WC 3.5 Komatiblität
- Bugfixes: Kompatibilität mit Product Bundles
- Option: Blacklist / Whitelist für Administratoren deaktivieren
- Global und Kundengruppen-Preise auch für Varianten (ohne die Notwendigkeit von Produktwerten)
- Handling von Umlauten und Sonderzeichen in Kundengruppen-Namen verbessert
- Angepasste Sprachdateien für Core und Plugin Improver
- Validierung für Negativwerten in allen Nummerfeldern
- Helper-Funktion zur Migration von 1.0.1 Staffelpreisen zu 1.0.2

= 1.0.3 =
- Preisberechnung auf Basis des regulären / Angebots-Preis für Gruppen und Staffelpreise
- Neue Ajax-Live-Price-Lösung für weniger Theme-Inkompatibilitäten
- Filterbare Preisausgabe für B2B Market Preise (Live-Preis und Single-Preis)
- Zahlreiche Performance-Optimierungen
- Netto-Preis-Anzeige im Warenkorb / Kasse bei B2B-Gruppen
- UVP-Preis-Anzeige
- WooCommerce-API-Nutzung für Registrierung
- Registrierung im Checkout / Bearbeitung der Felder im Kundenkonto
- Handelsregister-Nummer als optionales Feld in der B2B-Registrierung
- FILTER: Produkt-Typen über Filter anpassbar
- FILTER: Regulären statt Angebotspreis für Kalkulation
- FILTER: Produkt-Gruppenpreis forcieren
- Automatisches Transient-Handling ohne Performance-Optionen (Transient-Option im Admin entfernt)
- Admin-Cache-Option ergänzt
- Deaktivierung / Aktivierung von Preisberechnung für Gast und Kunde
- Deaktivierung Gast-Suche für bessere Performance
- HOOKS: Alle Klassen sind nun über entsprechende Hooks anpassbar/filterbar
- Kompatibilität WP Support Plugins
- Kompatiblität Postman SMTP
- Kompatibilität WooCommerce Product Addons (Filter-Ergänzung der Plugin-Entwickler steht noch aus)
- REST API Fix für Billbee und weclapp
- Avada Shortcode Handling Support
- WPAllImport Support Verbesserungen
- Elementor Whitelist/Blacklist Kompatibilität
- Flatsome Live-Preis Kompatibilität
- Shopkeeper Live-Preis Kompatibilität
- Avada Live-Preis Kompatibilität
- Erendo Live-Preis Kompatibilität
- Raidboxes Admin Cache Fix
- Safari Source .map ergänzt
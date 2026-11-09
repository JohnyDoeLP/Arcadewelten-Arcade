# Arcadewelten Arcade für phpBB 3.3

**Version 0.17.0-beta – in Entwicklung, bitte zuerst an einer Kopie des Forums testen.**

Selbst gehostete Spielhalle für phpBB 3.3: Spieleliste mit Kategorien, Bestenlisten, Pokale und Ruhmeshalle.
Keine Verbindungen nach außen, keine Werbung, kein Lizenzserver, keine Spiel-Limits. Open Source unter der GPL-2.0.

Schritt-für-Schritt-Anleitung: siehe `INSTALL.de.md`.

## Funktionen

- Rechte über phpBB: Benutzerrecht „Kann die Spielhalle benutzen“, Kategorien nur für bestimmte Benutzergruppen sichtbar (z. B. Ü18)
- Spieleliste mit Kategorie-Kacheln (Bild, Spielanzahl, Kategorie-Champion), Suche und Seitenaufteilung; optional Passwort pro Kategorie; 18 mitgelieferte Kategorie-Bilder, eigene Bilder einfach in `images/arcade/cats/` ablegen
- Kino-Modus („Licht aus“), Handy-/Touch-Spiele mit Vollbild-Start (auch auf dem iPhone), Leiste „Zuletzt gespielt“
- Favoriten (Herz), Medaillen 🥇🥈🥉 neben Beiträgen, „Spiel melden“ mit Liste im ACP, Massenbearbeitung von Mindestzeit, Höchstpunktzahl und Punkten
- Foren-Anbindung (ArcadeBot): neue Spiele und Saison-Top-10 automatisch als Forenbeiträge, Spielthema als Diskussionsthema, eigenes News-Forum pro Kategorie
- Duelle: Mitglieder fordern sich mit ihrem Rekord heraus, mit phpBB-Benachrichtigungen; „Neu“-Schild für frisch installierte Spiele
- Spiele bewerten: 1–5 Sterne pro Mitglied und Spiel (nur nach dem Spielen, änderbar), Durchschnitt in der Spieleliste; Spieleliste sortierbar nach Name, Bewertung, Beliebtheit und Neuheit
- Bestenliste pro Spiel mit Pokalen für Platz 1–3, Ruhmeshalle (Siegertreppchen mit Avataren)
- Rekorde im Forum: neben Beiträgen, im Profil, neueste Rekorde auf der Startseite
- Gäste sehen nur Spiele, die du für sie freigibst; keine Bestenlisten und keine geschützten Kategorien für Gäste
- Flash-Spiele über [Ruffle](https://ruffle.rs) (selbst gehostet, kein CDN)
- Import vorhandener Spiele aus einem Spiele-Ordner (Installationsdateien werden gelesen, nie ausgeführt); die Punkte-Schnittstelle wird aus der Spieldatei selbst erkannt
- Optional: Anbindung an [Ultimate Points](https://github.com/dmzx/Ultimate-Points-Extension) – Kosten pro Spiel, Belohnung pro Runde, Saisonprämien für Platz 1–3, monatlicher Saisonabschluss mit Vorschau
- Plausibilitätsprüfung gegen manipulierte Punkte: Mindestspielzeit und Höchstpunktzahl; verdächtige Ergebnisse landen in einer Prüfliste im ACP
- ACP: Einstellungen, Spiele, Kategorien, Prüfliste, Saisonabschluss, Spielezähler zurücksetzen, Diagnose-Protokoll
- DSGVO-freundlich: gespeichert werden je Mitglied und Spiel nur beste Punktzahl, Zeitpunkt und Anzahl der Versuche; Daten gelöschter Mitglieder werden mitgelöscht; passender Abschnitt in der Datenschutzerklärung des Forums wird automatisch ergänzt

## Unterstützte Punkte-Schnittstellen

| Spieltyp | Schnittstelle |
| --- | --- |
| Flash | IBPro (`index.php?act=Arcade&do=newscore`) |
| Flash | IBPro v3 / v32 (`index.php?autocom=arcade&do=…`) |
| Flash | Activity Mod (`newscore.php`) – dafür `contrib/root/newscore.php` in den Forum-Root kopieren |
| HTML5 | eigene Schnittstelle: das Spiel ruft `parent.awArcade.submitScore(punkte)` auf |

Nicht unterstützt: Spiele, die an den JV-Arcade-Server gebunden sind (HTML5-Pakete `*_jvh5`, `PHPBBARCADE_GAME`).

## Voraussetzungen

- phpBB 3.3.x
- PHP 8.2 oder neuer
- optional: Erweiterung Ultimate Points (dmzx)

## Installation

Kurzfassung (ausführlich mit Ordner-Übersicht in `INSTALL.de.md`):

1. Den Ordner `arcadewelten` nach `ext/` im Forum hochladen → `ext/arcadewelten/arcade/`.
2. Spiele nach `arcade/games/<name>/` im Forum-Root legen (je Spiel: `<name>.php`, `<name>.swf`, Bild).
   IBPro-v3-Spiele brauchen zusätzlich ihre Daten unter `arcade/gamedata/<name>/`.
3. ACP → Anpassen → Erweiterungen verwalten → „Arcadewelten Arcade“ aktivieren.
4. ACP → Erweiterungen → Spielhalle → Spiele → „Spiele aus dem Spiele-Ordner übernehmen“.
5. Nur für Activity-Mod-Spiele: `contrib/root/newscore.php` in den Forum-Root kopieren.

Die Spielhalle erreichst du unter `app.php/arcadehall` (mit URL-Rewriting unter `/arcadehall`) und über den Link „Spielhalle“ im Menü.

## Umstieg von JV phpBB Arcade

1. Datenbank sichern.
2. Im ACP „phpBB Arcade“ und „JVA Core“ deaktivieren und „Daten löschen“ wählen.
3. `ext/jv/` löschen. Im Ordner `arcade/` nur `games/` und `gamedata/` behalten.
4. Diese Erweiterung installieren und die Spiele importieren.
5. Optional: `contrib/root/arcade.php` in den Forum-Root kopieren (neben die `index.php`). Alte Links aus Beiträgen wie `arcade.php?mode=stats&u=2` führen dann zur Rekordseite des Mitglieds, andere alte Spielhallen-Links zur Spielhalle. Nur nötig, wenn noch viele alte Beiträge solche Links enthalten.

## HTML5-Spiele

Ein HTML5-Spiel liegt in `arcade/games/<name>/` mit `index.html`, einer Installationsdatei `<name>.php` mit `'game_type' => GAME_TYPE_HTML5` und einem Bild.
Bei Game Over ruft es auf:

```javascript
parent.awArcade.submitScore(endpunktzahl);
```

Spielsitzung und Formular-Token erledigt die Spielseite der Spielhalle.

## Geplant

Was als Nächstes kommt, steht in `ROADMAP.md`.

## Datenschutz

Die Spielhalle hängt an die Datenschutzerklärung des Forums (`ucp.php?mode=privacy`) automatisch einen Abschnitt „Spielhalle“ an. Der Text passt sich den Einstellungen an (Gäste, Plausibilitätsprüfung, Kategorie-Passwörter, Punkte, Diagnose) und lässt sich im ACP abschalten. Wer eine eigene Datenschutzerklärung pflegt, findet einen Textbaustein in `contrib/privacy.de.txt`.

## Bekannte Einschränkungen

- Flash-Spiele laufen über den Emulator Ruffle. Ältere Spiele (ActionScript 1/2) laufen gut, die Unterstützung für ActionScript 3 ist in Ruffle noch lückenhaft.
- Punkte aus Browser-Spielen lassen sich nie vollständig gegen Manipulation absichern. Die Spielhalle prüft Spielsitzungen, die IBPro-v3-Prüfsumme, eine Mindestspielzeit und optional eine Höchstpunktzahl pro Spiel.
- Spiele, die ihre Punkte aus einer ActionScript-Funktion heraus senden, speichern unter Ruffle ohne Patch keine Punkte. Solche Spiele mit `contrib/tools/ruffle_function_geturl_patch.py` patchen (Python 3, Original vorher sichern). Der Diagnose-Schalter im ACP zeigt bei betroffenen Spielen `super=…` / `arguments=`.

## Kategorie-Bilder

Die 18 Bilder in `images/cats/` wurden für Arcadewelten mit KI-Unterstützung (ChatGPT) erstellt und stehen unter **CC0** – sie dürfen ohne Einschränkung genutzt, verändert und weitergegeben werden, auch unabhängig von dieser Erweiterung. Sie enthalten keine fremden Figuren oder Marken.

## Lizenz

GPL-2.0-only, siehe `license.txt`.
Ruffle steht unter MIT / Apache-2.0, siehe `styles/all/theme/ruffle/LICENSE_*`.

© 2026 Patrick Overmann – [arcadewelten.eu](https://arcadewelten.eu)

# Installationsanleitung – Arcadewelten Arcade

Diese Anleitung führt dich Schritt für Schritt durch Installation, Spiele-Import und Einrichtung.
Zeitbedarf: etwa 15 Minuten plus Hochladen deiner Spiele.

> **Vorher:** Sichere deine Datenbank (ACP → Wartung → Datensicherung oder phpMyAdmin).
> Die Erweiterung ist eine Beta-Version – teste sie am besten zuerst an einer Kopie deines Forums.

## 1. Voraussetzungen prüfen

- phpBB **3.3.x** (ACP → Startseite → „Board-Version“)
- PHP **8.2 oder neuer** (ACP → Startseite → „PHP-Version“)
- FTP-Zugang zu deinem Webspace
- optional: Erweiterung **Ultimate Points** (dmzx), wenn Spiele Punkte kosten bzw. bringen sollen

## 2. Erweiterung hochladen

1. ZIP-Datei entpacken. Darin liegt der Ordner `arcadewelten`.
2. Den kompletten Ordner `arcadewelten` per FTP in den Ordner `ext/` deines Forums hochladen.
3. Danach muss es so aussehen:

```
dein-forum/
└── ext/
    └── arcadewelten/
        └── arcade/
            ├── composer.json
            ├── ext.php
            └── …
```

Liegt `composer.json` eine Ebene tiefer (z. B. `ext/arcadewelten/arcade/arcade/`), ist beim Entpacken ein Ordner zu viel entstanden – dann erkennt phpBB die Erweiterung nicht.

## 3. Erweiterung aktivieren

1. ACP → **Anpassen** → **Erweiterungen verwalten**
2. Bei „Arcadewelten Arcade“ auf **Aktivieren** klicken.
3. Im Menü erscheint jetzt der Link **Spielhalle**, im ACP unter **Erweiterungen** der Bereich **Spielhalle**.

## 4. Spiele hochladen

Lege deine Spiele im Forum-Root ab, jedes Spiel in einem eigenen Ordner:

```
dein-forum/
└── arcade/
    ├── games/
    │   └── tetris2/
    │       ├── tetris2.php    ← Installationsdatei
    │       ├── tetris2.swf    ← Spiel
    │       └── tetris2.gif    ← Bild
    └── gamedata/              ← nur für IBPro-v3-Spiele
        └── <spielname>/
```

- Die Installationsdateien im üblichen phpBB-Arcade-Format (`$game_data = array(...)`) werden verstanden.
- IBPro-v3-Spiele brauchen zusätzlich ihren Ordner unter `arcade/gamedata/`.
- Empfohlene Rechte: Ordner **755**, Dateien **644** (777 ist nicht nötig).

## 5. Spiele importieren

1. ACP → Erweiterungen → Spielhalle → **Kategorien**: Kategorien anlegen (z. B. Action, Puzzle, Klassiker).
2. ACP → Erweiterungen → Spielhalle → **Spiele**: Unten steht die Liste „Spiele aus dem Spiele-Ordner übernehmen“.
3. Gewünschte Spiele anhaken, Kategorie wählen, **Ausgewählte Spiele übernehmen**.
4. Rot markierte Spiele lassen sich nicht übernehmen (z. B. Spieldatei fehlt oder JV-eigene Schnittstelle).

Die Punkte-Schnittstelle wird beim Import direkt aus der Spieldatei erkannt. Du kannst sie bei jedem Spiel unter „Bearbeiten“ ändern.

## 6. Activity-Mod-Spiele (nur falls vorhanden)

Spiele mit der Schnittstelle „Activity Mod“ senden ihre Punkte an `newscore.php` im Forum-Root.
Kopiere dafür die Datei `ext/arcadewelten/arcade/contrib/root/newscore.php` in den Forum-Root (neben die `index.php`).
Unter ACP → Spielhalle → Einstellungen siehst du mit einem grünen Haken, ob sie richtig liegt.

## 7. Einstellungen

ACP → Erweiterungen → Spielhalle → **Einstellungen**:

| Einstellung | Empfehlung |
| --- | --- |
| Gäste dürfen freigegebene Spiele spielen | Ja – und einzelne Spiele per „für Gäste freigeben“ auswählen |
| Mindestspielzeit | 5 Sekunden (schnellere Ergebnisse kommen in die Prüfliste) |
| Hinweis „Spielhalle: Arcadewelten Arcade“ | gern an – Pflicht ist er nicht |
| Diagnose | nur zur Fehlersuche einschalten |
| Punkte (Ultimate Points) | Kosten, Rundenbelohnung, Saisonprämien – optional |

Pro Kategorie kannst du ein **Bild** (Pfad relativ zum Forum, z. B. `images/arcade/puzzle.png`) und ein **Passwort** setzen.
Pro Spiel kannst du **Mindestspielzeit**, **Höchstpunktzahl** und Punkte-Werte festlegen.

**Rechte:** Wer die Spielhalle benutzen darf, regelt das Benutzerrecht **„Kann die Spielhalle benutzen“** (ACP → Berechtigungen → Benutzer- oder Gruppenrechte, Reiter „Diverses“). Nach der Installation haben Gäste, registrierte Benutzer und Suchmaschinen (Gruppe „Bots“, sie sehen nur, was Gäste sehen) dieses Recht – es ändert sich also nichts, bis du es selbst anpasst. Eine Kategorie lässt sich außerdem auf bestimmte **Benutzergruppen** beschränken (z. B. eine Ü18-Kategorie nur für die Gruppe „Verifiziert“); alle anderen sehen sie dann gar nicht.

## 8. Datenschutzerklärung ergänzen

Ab Werk hängt die Spielhalle an die Datenschutzerklärung des Forums (`ucp.php?mode=privacy`) automatisch einen passenden Abschnitt an – du musst nichts tun. Abschalten kannst du ihn unter **Einstellungen → Abschnitt „Spielhalle“ in der Datenschutzerklärung**. Pflegst du eine eigene Datenschutzerklärung, findest du einen fertigen Textbaustein in `contrib/privacy.de.txt`.

## 9. Testen

1. Ein Spiel bis zum Ende spielen – danach muss die Bestenliste mit „Neuer persönlicher Rekord“ erscheinen.
2. Klappt das nicht: ACP → Einstellungen → **Diagnose** auf Ja, noch einmal spielen, dann ACP → Wartung → **Administrator-Protokoll** ansehen.
   - Kein Eintrag: Das Spiel hat keine Punkte gesendet.
   - Eintrag mit `super=…` / `arguments=`: Das Spiel sendet aus einer Funktion heraus → mit `contrib/tools/ruffle_function_geturl_patch.py` patchen (siehe README).
3. Diagnose danach wieder ausschalten.

## Update auf eine neue Version

1. ACP → Erweiterungen verwalten → „Arcadewelten Arcade“ **deaktivieren** (nicht „Daten löschen“!).
2. Den Ordner `ext/arcadewelten/` per FTP durch den neuen ersetzen.
3. Erweiterung wieder **aktivieren** – neue Datenbank-Felder werden automatisch angelegt.
4. ACP → Allgemein → **Cache leeren**.

## Umstieg von JV phpBB Arcade

1. Datenbank sichern.
2. „phpBB Arcade“ und danach „JVA Core“ im ACP deaktivieren und jeweils **Daten löschen** wählen.
3. Per FTP `ext/jv/` löschen. Im Ordner `arcade/` nur `games/` und `gamedata/` behalten.
4. Diese Erweiterung installieren (Schritte 2–9).

Hinweis: JV-eigene HTML5-Spiele (`*_jvh5`) und Spiele mit JV-Schnittstelle funktionieren nur mit dem JV-Server und lassen sich nicht übernehmen.

## Deinstallieren

ACP → Erweiterungen verwalten → deaktivieren → **Daten löschen**. Danach `ext/arcadewelten/` und ggf. `newscore.php` im Root löschen. Deine Spiele im Ordner `arcade/` bleiben erhalten.

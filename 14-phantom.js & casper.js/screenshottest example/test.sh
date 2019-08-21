#!/bin/bash

function TEST {
# If a script name is provided to ./test.sh, it will
# only run that script and ignore all the others.
  TEST=${1}
  if test -n "$SCRIPT" -a "$SCRIPT" != "$TEST"; then
    return
  fi
  shift

  if test "${1}" = "with_collectives"; then
    COLLECTIVES=1
    shift
    pushd 'C:/xampp/htdocs/OEQUASTA-Kollektivbildung'
    git clean -dXf
    python -u webservice.py &
    popd
  else
    COLLECTIVES=0
  fi

  mkdir -p logs
  LOGFILE=logs/`echo $TEST | sed 's/\.js//i'`.txt

  $CASPERJS test $TEST ${*} 2>&1 | tee $LOGFILE
  test "$SCRIPT" && exit

  if test $COLLECTIVES -eq 1; then
    kill %
  fi
}

function LOAD {
  drush sql:drop -y
  gzip -dc ${1} | `drush sql:connect`
  $CASPERJS test EntupHelper.js
}

PATH="/c/PhantomJS-2.1.1/bin:$PATH"
CASPERJS='C:/CasperJS-1.1.1/bin/casperjs.exe'
SCRIPT=${1}

if test -z "$SCRIPT"; then
  # alle Logs löschen, damit alte Tests nicht in errors.txt überleben
  rm -f logs/*

  ./install-drupal.sh
fi

{
  TEST Logout.js
  TEST Teilnehmerverwaltung.js
  TEST Anrede.js
  TEST Tarifabschlag.js
  TEST ExtranetZugriff.js
  TEST Durchgang.js
  TEST Nutzerkonto.js
  TEST Land.js
  TEST Bundesland.js
  TEST E-Mail-Versandliste.js
  TEST RechnungManuellErstellen.js
  TEST Jahresrechnungen.js
  TEST teilnehmer_und_subtyp.js
  TEST GeplanteFeldanpassung.js
  TEST Analyt.js
  TEST AnalyteEinesTeilnehmersAuflistenForm.js
  TEST Rundversuchsprogramm.js
  TEST NeuerSubtyp.js
  TEST NeueAnalytklasse.js
  TEST NeueProbenoption.js
  TEST Flaeschchenverwaltung.js
  TEST Scoregruppen.js
  TEST Posteingang.js
  TEST AttributeEinesTeilnehmers.js
  TEST E-Mail-Benachrichtigungen.js
  TEST aerztekammer_aenderungsmitteilung.js
  TEST UebersichtRundversuche.js
  TEST AttributAnlegen.js

  # funktionieren nicht
  #TEST RohdatenImport_Apfel.js
  #TEST RohdatenImport_Roeteln.js
  #TEST RohdatenImport_Autoimmunologie.js
  #TEST RecoScan.js

  TEST TeilweiseBezahlteRechnungen.js
  TEST NominaleAntwortTest.js
  TEST DurchgangStarten.js with_collectives

  LOAD dbReports.sql.gz
  TEST Reports.js with_collectives

  LOAD dbReports.sql.gz
  TEST Stammdaten.js

  LOAD dbGuetezeichen.sql.gz
  TEST RECOscan2.js

  LOAD dbGuetezeichen.sql.gz
  TEST DurchgangNachJahr.js

  LOAD dbGuetezeichen.sql.gz

  # funktioniert nicht
  #TEST AngemeldeteAnalyte.js

  TEST Eingebbar.js
  TEST Guetezeichen.js
  TEST RohdatenExport.js with_collectives
} | while read LINE; do
# Color each line according to case-insensitive keyword matches.
# ${LINE,,} is $LINE converted to lower case,
# [[ $TEXT =~ $REGEXP ]] returns true if $REGEXP matches $TEXT
  [[ ${LINE,,} =~ selector|datenbank|test\ file ]] && echo -ne "\e[92m"
  [[ ${LINE,,} =~ 'capture saved to' ]] && echo -ne "\e[96m"
  [[ ${LINE,,} =~ error|fail ]] && echo -ne "\e[91m"
  [[ ${LINE,,} =~ warning|achtung ]] && echo -ne "\e[33m"
  echo -n $LINE
  echo -e "\e[39m"
done

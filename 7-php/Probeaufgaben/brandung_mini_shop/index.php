<?php
/**
 * Created by PhpStorm.
 * User: Besitzer
 * Date: 17.11.2018
 * Time: 11:22
 */
foreach (glob("class/*.php") as $filename)
{
        include $filename;
}
$user = new User('Serhiy', '1234');
$user->printUserName();
$user->printUserPasswort();
$kategorien = new Kategorien();
$kategorien->printKategorien();
$kategorien->setKategorien("Blumen");
$kategorien->printKategorien();
$produkten = new Produkte();
$produkten->getProdukten(1);
//user wählt z.B BMW X5 in warenkorb
$warenkorb = new Warenkorb();
$warenkorb->setWarenkorb(1);
$warenkorb->printWarenkorbIds();
$warenkorb->setWarenkorb(2);
$warenkorb->printWarenkorbIds();
//missklick, use möchte kein Opel Astra
$warenkorb->deleteWarenkorbId(2);
$warenkorb->printWarenkorbIds();
$bestellung = new Bestellungen();
$bestellung->setQuitungNr();
$bestellung->printQuitungNr();
$bestellung->sendmail($user->getUserName());
?>
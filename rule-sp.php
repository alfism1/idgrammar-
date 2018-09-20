<?php
$ruleSubject = [
    // PRP : Saya, Mereka, Dia, Kamu
    "/~wd[A-z \-]+wd~~t[A-z,]+(PRP)[A-z,]+g~/i",
    // NN : Baju
    "/~wd[A-z \-]+wd~~t[A-z,]+(NN)[A-z,]+g~/i",
    // NN Ini/Itu : Baju Saya
    "/~wd[A-z \-]+wd~~t[A-z,]+(NN)[A-z,]+g~ ~wd[A-z ]+wd~~t[A-z,]+(PRP)[A-z,]+g~/i",
    // NN Ini/Itu : Baju Ini/Itu
    "/~wd[A-z \-]+wd~~t[A-z,]+(NN)[A-z,]+g~ ~wd(ini|itu)wd~~t[A-z,]+g~/i",
    // NN JJ : Baju Merah
    "/~wd[A-z \-]+wd~~t[A-z,]+(NN)[A-z,]+g~ ~wd[A-z ]+wd~~t[A-z,]+(JJ)[A-z,]+g~/i",
    // NN JJ PRP : Baju Merah Saya
    "/~wd[A-z \-]+wd~~t[A-z,]+(NN)[A-z,]+g~ ~wd[A-z ]+wd~~t[A-z,]+(JJ)[A-z,]+g~ ~wd[A-z ]+wd~~t[A-z,]+(PRP)[A-z,]+g~/i",
    // NN JJ NN PRP : Baju Merah Adik Saya
    "/~wd[A-z \-]+wd~~t[A-z,]+(NN)[A-z,]+g~ ~wd[A-z ]+wd~~t[A-z,]+(JJ)[A-z,]+g~ ~wd[A-z ]+wd~~t[A-z,]+(NN)[A-z,]+g~ ~wd[A-z ]+wd~~t[A-z,]+(PRP)[A-z,]+g~/i",
    // NN JJ NN PRP Ini/Itu : Baju Merah Adik Saya Ini/Itu
    "/~wd[A-z \-]+wd~~t[A-z,]+(NN)[A-z,]+g~ ~wd[A-z ]+wd~~t[A-z,]+(JJ)[A-z,]+g~ ~wd[A-z ]+wd~~t[A-z,]+(NN)[A-z,]+g~ ~wd[A-z ]+wd~~t[A-z,]+(PRP)[A-z,]+g~ ~wd(ini|itu)wd~~t[A-z,]+g~/i",
];

$rulePredikat = [
    // VB
    "/~wd[A-z \-]+wd~~t[A-z,]+(VB)[A-z,]+g~/i",
    // JJ
    "/~wd[A-z \-]+wd~~t[A-z,]+(JJ)[A-z,]+g~/i",
];
?>
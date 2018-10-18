<?php 

require_once 'src/gtranslate.php';

$gt=new gtranslate();

//translate short sentances using Google translate web

//example of usage 
// first parm is text (string), second "to language" and third, "from language" if you miss third parm auto detection will be applied
echo $gt->translate('Hello, how are you?', 'mk','en');

echo '<hr>';

//example of usage with caching - this will call google only if string wasn't translated before
//Recommended for production
echo $gt->translate('Hello, how are you?', 'mk','en',true);


/*
 * USE THESE shortcuts as languages
 * 
af - Afrikaans
sq - Albanian
ar - Arabic
be - Belarusian
bg - Bulgarian
ca - Catalan
zh-CN - Chinese
hr - Croatian
cs - Czech
da - Danish
nl - Dutch
en - English
et - Estonian
tl - Filipino
fi - Finnish
fr - French
gl - Galician
de - German
el - Greek
iw - Hebrew
hi - Hindi
hu - Hungarian
is - Icelandic
id - Indonesian
ga - Irish
it - Italian
ja - Japanese
ko - Korean
lv - Latvian
lt - Lithuanian
mk - Macedonian
ms - Malay
mt - Maltese
no - Norwegian
fa - Persian
pl - Polish
pt - Portuguese
ro - Romanian
ru - Russian
sr - Serbian
sk - Slovak
sl - Slovenian
es - Spanish
sw - Swahili
sv - Swedish
th - Thai
tr - Turkish
uk - Ukrainian
vi - Vietnamese
cy - Welsh
yi - Yiddish
 * 
 */
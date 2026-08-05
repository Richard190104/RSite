# Cisto pre KJNTSSNP README

### Aplikacia funguje takto:
- existuje route /admin. Ta obsahuje konfiguraciu celeho webu, jeho stranok, noviniek ...
- Zakladom su pages. Tie obsahuju vsetky konfiguracie stranok - dynamicke kategorie, texty.. (napr. home na home stranke chceme aby si sami vedeli nastavit text "O nás", je to nastavitelne v adminovi -> pages -> home). Kazda page ma vlastne konfiguracne veci ktore pri implementacii treba tahat odtial.
- "Texty" v adminovi su basically common texty, ktore sa budu pouzivat napriec strankami (napr. text 'organizacia' obsahuje hodnotu "xxx". To budeme zobrazovat vsade tam kde bude potrebne to zobrazit. NEBUDEME TO NATVRDO PISAT DO KODU - radsej dynamicky cez "texts->organisation").
- Kategorie navigacie su self-explanatory. V adminovi sa vytvori kategoria navigacie, priradi sa im stranka(y). Podla toho sa bude navbar vykreslovat.
- Bannery - su definovane podla toho ako si ich dame. Zamyslane to bolo ako banner na uvode stranky - obrazok s textom hned co vidis hore ked otvoris stranku. No daju sa pouzit aj ako minibannery alebo ine bannery. Staci pridat VIRTUAL_LOCATION. To nie je v DB, len v kode. Pridam VR location, ulozim novy banner do db s touto novou location, potom podmienkujem - ked je banner location == "xx" tak zobrazim.
- Novinky - jednoducha DB table s napojenim na kategorie. Vytvorim kategoriu, podla nej viem spravit ribbon alebo ju nejako oznacit - v admine select kategorie.
- Kategorie - asi najtazsi mechanizmus. Samotne kategorie obsahuju len nazov a optional parent category + ci sa zobrazuju v galerii alebo nie. No pouzivaju sa na rozne veci:
1. Novinky - pouzivaju ich na jednoduchy ribbon pod ktoru kategoriu novinka patri, nic ine.
2. Galérie - Viem pridat fotku a zaradit ju do kategorie. Podla toho ci je v kategorii zapnute zobrazenie tak sa bud ukaze alebo nie.
3. Podujatia - Obsahuju pole kategoria preto, aby sa dali zobrazit fotky z daneho podujatia. Takze napr. pridam fotky ktorym dam kategoriu xx, tej nastavim aby sa nezobrazovala v galerii a prepojim ju sem. Zobrazia sa mi fotky z nej len tu. Ked ten config na zobrazenie vypnem tak bude aj tu aj v galerii.

### Admin je ready
Vsetko popisane by malo byt ready do templatu. Ak najdes nieco co logicky nefunguje, tak upravime.
Ked preskumas DB zistis co kde je ako ulozene, no malo by to byt vsetko ready na posielanie do UI.

### Your job
Potrebujeme redizajn stranky mosrz. Tu je nejaky base: https://www.figma.com/design/XHigFbZix9yKc4G9sRv92l/Untitled?node-id=1-124&t=H8jiT1mQ4w2ACthJ-0
Je to not ideal ale nieco z toho vymyslime. Ked identifikujes ze potrebujeme nieco pridat do admina - napr. vela krat sa tam opakuje nazov stranky, pridame tam konfiguracne pole a pouzijeme to, nech pri zmene nedojde k tomu ze to musime prepisovat na 100 miestach.

Je to cele nasadene na free hosting stranke, ktora ma urcite obmedzenia:
1. Ako pushovat na prod? - Bud ti nastavim sam tuto vec, alebo jednoducho pushnes na github a povies mi nech to nasadim. Nastavil som si FreeZilla ktory dokaze uploadovat na prod s tym, ze sa nedaju spustat migracky. Ked spravis migracku a chces nasadit tak mi povedz, pripadne steps su taket - das na prod script vo webrooote ktory sa vola run-migrations, otrovis url https://rsite.great-site.net/run-migrations.php?token=TVOJ_TAJNY_TOKEN - token si definujes v subore. potom hned zmazat kvoli security.
2. Vsetky zmeny klasicky na github - vytvaraj branches pre kazdu zmenu (napr. fix- xxx, feature- xxx, update- xxx). Pojdeme v code review style - pushnes branch na github, vytvoris PR, pozriem sa na to a poviem ci to mozes mergnut prip. dat nejake upravy. To iste plati pre mna - base features pojdu PR na teba. Po urcitych zmenach ked to bude na mastri ja to nahram na prod.


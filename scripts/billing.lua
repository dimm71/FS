local accountcode = argv[1]; -- Для проверки не из FreeSwitch пишем переменные так arg[1]
local destination = argv[2];
local domain = argv[3];
--
--  Подключение к Postgres с помощью Lua. Установить apt install lua-sql-postgres
local luasql = require "luasql.postgres"
--
-- Настройки подключения к базе PostgreSQL
DBHOST = '127.0.0.1'
DBNAME = 'freeswitch'
DBUSER = 'freeswitch'
DBPASS = 'professional'
DBPORT = '5432'

--Получаем стоимость минуты звонка по направлению и баланс
local env = assert (luasql.postgres())
local dbcon = env:connect(DBNAME, DBUSER, DBPASS, DBHOST, DBPORT)
res = dbcon:execute("SELECT * FROM destinations WHERE '".. destination .."' LIKE CONCAT(prefix,'%') ORDER BY CHAR_LENGTH(prefix) DESC OFFSET 0 LIMIT 1")
readsql = dbcon:execute("SELECT * FROM extensions WHERE accountcode='".. accountcode .."' AND domain='".. domain .."'")
row = res:fetch ({}, "a")
row1 = readsql:fetch ({}, "a")
-- Получаем и присваеваем значение столбца zone_id (example https://keplerproject.github.io/luasql/examples.html)
local data = row.zone_id
local balance = row1.balance
readsql:close()
res:close()
dbcon:close()
env:close()

function update()
bill_min = math.ceil(billsec / 60); -- округляем в большую сторону
newbalance = (balance - (bill_min * data1));
local env = assert (luasql.postgres());
local dbcon = env:connect(DBNAME, DBUSER, DBPASS, DBHOST, DBPORT)
sql = "UPDATE extensions SET balance='".. newbalance .."' WHERE accountcode='".. accountcode .."' AND domain='".. domain .."'"
res, serr = dbcon:execute(sql);
dbcon:close();
env:close();
end;

balance1 = "1.7"
data1 = "1.93"
--billsec = 241;
access_min = math.floor(balance / data1); -- округляем в меньшую сторону
access_sec = access_min * 60;


if (balance >= data1)  then
  freeswitch.consoleLog("WARNING","before first leg answered\n");
--session:answer();
  legA = freeswitch.Session("{sip_cid_type=none,absolute_codec_string='PCMA,PCMU',execute_on_answer='sched_hangup +".. access_sec .." alloted_timeout'}sofia/gateway/qwerty/" .. destination);
  freeswitch.bridge(session, legA);
  freeswitch.consoleLog("info",legA:getVariable("billsec"));
  billsec = legA:getVariable("billsec");
    update();
else
  session:answer();
  session:setAutoHangup(false);
  session:sleep(1000);
  session:execute("playback", "shout://tts.voicetech.yandex.net/tts?format=mp3&quality=hi&platform=web&application=translate&lang=ru_RU&speaker=ermil&emotion=good&text=${url_encode Здравствуйте. Ваш баланс состовляет ".. balance1 .." рублей. Вы не можете совершить вызов. Пополните ваш баланс.");
  session:hangup();
end;

--------------------------------------------------------------------------------------------------------------------------------------
--print(balance)
--print(access_min)
--print(bill_min)
--print(newbalance)
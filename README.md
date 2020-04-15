# asterisk-tts

------------
Instalação
------------

`cd /usr/src/`  
`git clone https://github.com/mrpbueno/asterisk-tts.git`  
`cp asterisk-tts/agi-bin/*.php /var/lib/asterisk/agi-bin`  
`fwconsole chown`  

------------
Teste
------------
Adicionar no arquivo `/etc/asterisk/extensions_custom.conf`  

`exten => 1234,1,Answer`  
`exten => 1234,n,agi(watson-tts.php,"Este é o mecanismo de conversão de texto em voz do IBM Watson.")`  
`exten => 1234,n,Goto(app-blackhole,hangup,1)`  

`exten => 4567,1,Answer`  
`exten => 4567,n,agi(google-tts.php,"Este é o mecanismo de conversão de texto em voz do Google.")`  
`exten => 4567,n,Goto(app-blackhole,hangup,1)`  

Após salvar o arquivo recarregue as configurações `fwconsole reload`  

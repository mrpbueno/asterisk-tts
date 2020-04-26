# asterisk-tts

## Antes de começar
Acesse a página [Text to Speech](https://cloud.ibm.com/catalog/services/text-to-speech) no catálogo do IBM Cloud.  
Inscreva-se para obter uma conta gratuita do IBM Cloud ou efetue login.  
Clique em Criar.  
Na [Lista de recursos do IBM Cloud](https://cloud.ibm.com/resources), clique em sua instância de serviço do Text to Speech e acesse a página do painel de serviço do Text to Speech.  
Na página Gerenciar, clique em Mostrar para visualizar suas credenciais.  
Copie os valores de API Key e URL.  

## Instalação
`cd /usr/src/`  
`git clone https://github.com/mrpbueno/asterisk-tts.git`  
`cp asterisk-tts/agi-bin/*.php /var/lib/asterisk/agi-bin`   
Adicione as suas credenciais do serviço do Text to Speech no arquivo:  
`/var/lib/asterisk/agi-bin/watson-tts.php`  
Ajuste as permissões dos arquivos  
`fwconsole chown`  

## Teste
Adicionar no arquivo `/etc/asterisk/extensions_custom.conf`  

`exten => 1234,1,Answer`  
`exten => 1234,n,agi(watson-tts.php,"Este é o mecanismo de conversão de texto em voz do IBM Watson.")`  
`exten => 1234,n,Goto(app-blackhole,hangup,1)`  

`exten => 4567,1,Answer`  
`exten => 4567,n,agi(google-tts.php,"Este é o mecanismo de conversão de texto em voz do Google.")`  
`exten => 4567,n,Goto(app-blackhole,hangup,1)`  

Após salvar o arquivo recarregue as configurações `fwconsole reload`  

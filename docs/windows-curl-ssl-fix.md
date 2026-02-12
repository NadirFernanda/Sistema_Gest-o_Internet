# Corrigir "cURL error 60: SSL certificate problem" no Windows (PHP / Guzzle)

Este passo-a-passo resolve o erro que aparece quando o cURL/PHP não encontra o CA bundle (cacert.pem):

1) Baixar o CA bundle oficial

 - Abra: https://curl.se/ca/cacert.pem
 - Salve o ficheiro como `cacert.pem` num local do seu sistema, por exemplo:
   - `C:\php\extras\ssl\cacert.pem`
   - ou `C:\Users\<seu_usuario>\.certs\cacert.pem`

2) Identificar o `php.ini` que o seu ambiente usa

 - Linha de comando (CLI):
   ```powershell
   php --ini
   ```
 - No servidor web (php-fpm / Apache) crie um `phpinfo()` temporário ou verifique `phpinfo()` em `/status` para ver `Loaded Configuration File`.

3) Editar `php.ini` (CLI e FPM/Apache) e apontar para o `cacert.pem`

 - Abra o `php.ini` identificado e adicione/edite as linhas (use o caminho que escolheu):
   ```ini
   curl.cainfo = "C:\\php\\extras\\ssl\\cacert.pem"
   openssl.cafile = "C:\\php\\extras\\ssl\\cacert.pem"
   ```
 - Observação: use barras duplas ou barras normais corretamente conforme o `php.ini`.

4) Reiniciar o PHP e o servidor web

 - Se estiver a usar PHP-FPM + Nginx no Windows (ou no WSL), reinicie o serviço. Em Windows IIS reinicie o serviço do PHP ou IIS:
   - Windows (IIS): reinicie o IIS Manager ou o Application Pool associado.
   - CLI: não precisa reiniciar, mas para o FPM/serviço web é necessário.

5) Testar que o PHP agora vê os certificados

 - Teste rápido em CLI:
   ```powershell
   php -r "var_dump(openssl_get_cert_locations());"
   ```
   Verifique se `default_cert_file` ou `default_cert_dir` aponta para o ficheiro que configurou.

 - Teste uma chamada cURL com PHP:
   ```php
   <?php
   $ch = curl_init('https://www.google.com');
   curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
   $res = curl_exec($ch);
   if (curl_errno($ch)) {
       echo 'ERR: ' . curl_error($ch);
   } else {
       echo 'OK';
   }
   curl_close($ch);
   ```

6) Se o erro persistir (diagnóstico rápido)

 - Verifique `php --ini` para garantir que editou o `php.ini` correcto.
 - Verifique permissões do ficheiro `cacert.pem`.
 - Em ambientes com containers/WSL, certifique-se de que o ficheiro está disponível dentro do ambiente do container.

7) Solução temporária (apenas para teste)

 - No Guzzle / Http client do Laravel, desactive `verify` (NÃO usar em produção):
   ```php
   Http::withOptions(['verify' => false])->post('https://api.ultramsg.com/instanceXXXX/messages/chat', $data);
   ```

8) Notas finais

 - Esta correção é a adequada para ambientes Windows locais. Em servidores Linux/Ubuntu normalmente o `ca-certificates` do sistema já resolve isto (`sudo apt install ca-certificates`).
 - Se preferir, posso criar um script PowerShell que baixa `cacert.pem`, faz backup do `php.ini` e aplica as alterações automaticamente — quer que eu gere esse script?

# palantirBot-v2
## Meta Inicial: Rebuildear el bot usando Slim y base de datos para los comandos

Comienza el Rebuild del Bot. La idea es utilizar Slim para faciliar Log de eventos y uso de base de datos para poder disparar comandos que necesiten accesos a Root. 

Hay que revisar si slim permite el una salida directo a telegram sin salir por el output standard. De no permitirlo la estuctura sería.

- Index que recibe los comandos
- Función para que envíe los mensajes (tipo función saludo)
- todo el procesamiento de los mensajes quedaría bajo slim respondiendo un json a la función de envio de mensajes.

En la base de datos se puede incluir una tabla de parámetros, para poder almacenar determinados elementos de configuración como ser modo debug, contraseña de acceso cifradas, etc.

**07/06/2020**: Se crea el repo, se plantea la meta y se clona para comenzar con el desarrollo.

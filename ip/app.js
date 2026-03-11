function generarZPL(ip, mac) {
    const printOption = document.getElementById('printOption').value;
    
    if (!ip) {
        alert("Por favor, ingrese la Dirección IP.");
        return null;
    }
    
    // Si se requiere MAC pero no se proporcionó, salir.
    if (printOption === 'ip_mac' && !mac) {
        alert("El formato seleccionado requiere la Dirección MAC.");
        return null;
    }

    // ------------------------------------------------------------------
    // Configuración Base ZPL para 3 etiquetas de 30mm x 14mm
    // ------------------------------------------------------------------
    const ZPL_BASE = "^XA^PW720^LL112^CI28";
    const ZPL_END = "^XZ";
    const OFFSET_STEP = 270; // Desplazamiento horizontal entre etiquetas

    // Genera el bloque de contenido ZPL para una sola etiqueta, basado en la opción.
    function getEtiquetaBlock(x_offset) {
        let block = "";
        
        if (printOption === 'ip_mac') {
            // == FORMATO IP Y MAC (Ajustado y compacto) ==
            block +=
                "^FO" + (x_offset + 3) + ",5^A0N,10,10^FDIP Address:^FS" +
                "^FO" + (x_offset + 3) + ",20^A0N,15,15^FD" + ip + "^FS" + 
                "^FO" + (x_offset + 3) + ",45^A0N,10,10^FDMAC Address:^FS" +
                "^FO" + (x_offset + 3) + ",60^A0N,15,15^FD" + mac + "^FS"; 
        } else {
            // == FORMATO SOLO IP (Texto más grande, centrado verticalmente) ==
            // Usamos un tamaño de fuente mayor (30x30) para rellenar la etiqueta
            block +=
                "^FO" + (x_offset + 3) + ",10^A0N,15,15^FDIP Address:^FS" +
                // Se centra el valor IP en la etiqueta más grande
                "^FO" + (x_offset + 3) + ",35^A0N,30,30^FD" + ip + "^FS"; 
        }
        return block;
    }
    
    // Ensamblaje de las 3 etiquetas
    let zpl = ZPL_BASE;
    
    zpl += getEtiquetaBlock(0);
    zpl += getEtiquetaBlock(OFFSET_STEP);
    zpl += getEtiquetaBlock(OFFSET_STEP * 2);

    zpl += ZPL_END; 
    
    return zpl;
}

// La función imprimirEtiqueta() se mantiene igual, ya que ahora llama a la nueva generarZPL
// Asegúrate de que tu función imprimirEtiqueta() en app.js llame a esta nueva generarZPL
// y envíe el resultado (zplCode) a la impresora.

function imprimirEtiqueta() {
    var ip = document.getElementById('ipAddress').value.trim();
    var mac = document.getElementById('macAddress').value.trim().toUpperCase();

    var zplCode = generarZPL(ip, mac);
    if (!zplCode) return; // Salir si la validación falla

    // 1. Verificar si la API de Browser Print está disponible
    if (typeof BrowserPrint === 'undefined') {
        alert("Error: La librería BrowserPrint.js no está cargada. Asegúrese de que el archivo esté en su servidor y esté enlazado en index.html.");
        return;
    }

    // 2. Intentar obtener la impresora predeterminada
    BrowserPrint.getDefaultDevice('printer', function(printer) {
        if (printer) {
            // 3. Enviar el código ZPL a la impresora
            printer.send(zplCode, 
                // Función de éxito
                function() {
                    //console.log("Etiqueta enviada correctamente a la impresora.");
                    //alert("Etiqueta enviada correctamente.");
                    
                    // ❌ LÍNEAS ELIMINADAS O COMENTADAS:
                    // document.getElementById('ipAddress').value = ''; 
                    // document.getElementById('macAddress').value = '';
                    
                    // La IP y MAC permanecerán en los campos de entrada.
                }, 
                // Función de error
                function(error) {
                    console.error("Error al enviar el comando ZPL:", error);
                    alert("Error al imprimir. Verifique la conexión de la impresora y el servicio Browser Print. Mensaje: " + error);
                }
            );
        } else {
            alert("Impresora no encontrada. Asegúrese de que la Zebra ZD220 esté configurada como la impresora predeterminada en Zebra Browser Print y que el servicio esté activo.");
        }
    }, function(error) {
        console.error("Error al intentar conectarse a la impresora:", error);
        alert("Error de conexión con Browser Print. Asegúrese de que el servicio esté corriendo.");
    });
}

// Función que se dispara con el menú selector (onchange)
function toggleMacInput() {
    const option = document.getElementById('printOption').value;
    const macContainer = document.getElementById('macContainer');
    const macInput = document.getElementById('macAddress');
    
    // Si la opción seleccionada es 'ip_mac' (IP y MAC)
    if (option === 'ip_mac') {
        // MOSTRAR Y HABILITAR
        macContainer.style.maxHeight = '100px'; // Muestra suavemente (para la animación CSS)
        macContainer.style.opacity = '1';
        
        macInput.removeAttribute('disabled');
        macInput.setAttribute('required', 'required');
        macInput.value = ''; // Opcional: Limpia el valor al re-habilitar
        
    } else {
        // OCULTAR (Deshabilitar y Atenuar)
        
        // Deshabilita el input para que no se pueda interactuar y no se envíe en formularios
        macInput.setAttribute('disabled', 'disabled'); 
        macInput.removeAttribute('required');
        
        // Mantiene el contenedor visible pero deshabilitado visualmente
        macContainer.style.opacity = '0.5'; // Atenúa el contenedor al 50%
        
        // Opcional: Ocultar completamente si prefieres el deslizamiento (como en la versión anterior)
        // macContainer.style.maxHeight = '0'; 
    }
}

// Llama a la función al cargar la página para establecer el estado inicial
document.addEventListener('DOMContentLoaded', toggleMacInput);
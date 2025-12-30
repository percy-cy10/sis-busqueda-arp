  <template>
      <iframe v-if="ver" :src="data" width="100%" height="100%"></iframe>
      <q-btn v-else :color="$q.dark.isActive? 'red-6':'negative'" :label="$q.screen.lt.sm || vericon? '' : label" :icon-right="vericon?'':'picture_as_pdf'"
        @click="Evento" :loading="loading" :disable="loading">
        <q-tooltip anchor="top middle" self="bottom middle" :offset="[10, 10]">
            Ver en PDF
          </q-tooltip>
      </q-btn>
  </template>

  <script setup>
  import html2canvas from "html2canvas";
  import QRCode from 'qrcode'
  import jsPDF from "jspdf";
  import { convertDate } from "src/utils/ConvertDate";
  import { formatNumberToSoles } from "src/utils/ConvertMoney";
  import { useQuasar } from "quasar";
  import { onMounted, ref } from "vue";
  import PagoService from "src/services/PagoService";

  const $q = useQuasar();
  const emits = defineEmits(["clickPDF"]);

  const props = defineProps({
    ver:{default:false},
    datosSolicitudRow:{default:null},
    datosBusqueda:{default:null},
    datosVerificacion:{default:null},
    datosPagos:{default:null},
    label:{default:'Generar PDF'},
    vericon:{default:false},
  });

  const data = ref(null);
  const loading = ref(false);

  onMounted(async ()=>{
    if (props.ver) {
      await VerificaDatos();
    }
  });

  function limitarLineas(Doc,Text,maxWidth,num_lineas){
    const lineas = Doc.splitTextToSize(Text, maxWidth);
    const lin = [];
    for (let index = 0; index < lineas.length; index++) {
      if (index+1<=num_lineas)
        lin[index] = lineas[index];
      else
        break;
    }
    return lin;
  }

  // =============================================
  // FUNCIONES PARA DIFERENTES TIPOS DE TRÁMITE
  // =============================================

  /**
   * Genera contenido específico para escrituras
   */
  function generarContenidoEscrituras(doc, datos, maxWidth, montoBusqueda, boletaPago, estadoPago) {
    const leftMargin = 20;
    let yPos = 80;

    doc.text("DATOS DE LA ESCRITURA:", leftMargin, yPos);
    doc.line(leftMargin, yPos + 2, 70, yPos + 2);
    yPos += 10;

    doc.text("Escritura Pública", 25, yPos);
    doc.text(": " + limitarLineas(doc, datos.sub_serie || '', 120, 2), 60, yPos, { align: "justify", maxWidth: 130 });
    yPos += 10;

    doc.text("Notario Público", 25, yPos);
    doc.text(": " + limitarLineas(doc, datos.notario || '', 120, 2), 60, yPos, { align: "justify", maxWidth: 130 });
    yPos += 10;

    doc.text("Otorgado por", 25, yPos);
    doc.text(": " + limitarLineas(doc, datos.otorgantes || '', 120, 2), 60, yPos, { align: "justify", maxWidth: 130 });
    yPos += 10;

    doc.text("A Favor de", 25, yPos);
    doc.text(": " + limitarLineas(doc, datos.favorecidos || '', 120, 2), 60, yPos, { align: "justify", maxWidth: 130 });
    yPos += 10;

    const mes = datos.mes ? datos.mes + ', ' : '';
    const dia = datos.dia ? datos.dia + ' de ' : '';
    doc.text("Lugar y Fecha", 25, yPos);
    doc.text(": " + (datos.ubigeo_soli || '') + "; " + dia + mes + (datos.anio || ''), 60, yPos, { align: "justify", maxWidth: 130 });
    yPos += 10;

    if (datos.bien) {
      doc.text("Nombre del Bien", 25, yPos);
      doc.text(": " + limitarLineas(doc, datos.bien, 120, 2), 60, yPos, { align: "justify", maxWidth: 130 });
      yPos += 10;
    }

    return yPos;
  }

  /**
   * Genera contenido específico para partidas
   */
  function generarContenidoPartidas(doc, datos, maxWidth, montoBusqueda, boletaPago, estadoPago) {
    const leftMargin = 20;
    let yPos = 80;

    doc.text("DATOS DE LA PARTIDA:", leftMargin, yPos);
    doc.line(leftMargin, yPos + 2, 70, yPos + 2);
    yPos += 10;

    // Mostrar tipo de partida
    const tipoPartidaLabels = {
      'defuncion': 'Partida de Defunción',
      'nacimiento': 'Partida de Nacimiento',
      'matrimonio': 'Partida de Matrimonio'
    };

    doc.text("Tipo de Partida", 25, yPos);
    doc.text(": " + (tipoPartidaLabels[datos.tipo_partida] || datos.tipo_partida || ''), 60, yPos);
    yPos += 10;

    // Campos específicos según el tipo de partida
    if (datos.tipo_partida === 'defuncion' && datos.nombre_fallecido) {
      doc.text("Nombre del Fallecido", 25, yPos);
      doc.text(": " + limitarLineas(doc, datos.nombre_fallecido, 120, 2), 60, yPos, { align: "justify", maxWidth: 130 });
      yPos += 10;
    }

    if (datos.tipo_partida === 'nacimiento' && datos.nombre_nacido) {
      doc.text("Nombre del Nacido", 25, yPos);
      doc.text(": " + limitarLineas(doc, datos.nombre_nacido, 120, 2), 60, yPos, { align: "justify", maxWidth: 130 });
      yPos += 10;
    }

    if (datos.tipo_partida === 'matrimonio') {
      if (datos.nombre_esposo) {
        doc.text("Nombre del Esposo", 25, yPos);
        doc.text(": " + limitarLineas(doc, datos.nombre_esposo, 120, 2), 60, yPos, { align: "justify", maxWidth: 130 });
        yPos += 10;
      }
      if (datos.nombre_esposa) {
        doc.text("Nombre de la Esposa", 25, yPos);
        doc.text(": " + limitarLineas(doc, datos.nombre_esposa, 120, 2), 60, yPos, { align: "justify", maxWidth: 130 });
        yPos += 10;
      }
    }

    // Generar línea "MUNICIPALIDAD: -- ; FECHA: --"
    const dia = datos.dia ? datos.dia + ' de ' : '';
    const mes = datos.mes ? datos.mes + ', ' : '';
    const anio = datos.anio || '';
    const municipalidad = datos.municipalidad_ubigeo || datos.ubigeo_soli || '';

    // 🟩 Texto completo en una sola línea
    const textoMunicipalidadFecha = `MUNICIPALIDAD: ${municipalidad} ; FECHA: ${dia}${mes}${anio}`;

    // 🖨️ Imprimir directamente el texto en el PDF
    doc.text(textoMunicipalidadFecha, 25, yPos, {
      align: "justify",
      maxWidth: 170
    });

    // Espaciado inferior
    yPos += 10;

    return yPos;
  }

  /**
   * Genera contenido específico para expedientes
   */
  function generarContenidoExpedientes(doc, datos, maxWidth, montoBusqueda, boletaPago, estadoPago) {
    const leftMargin = 20;
    let yPos = 80;

    doc.text("DATOS DEL EXPEDIENTE:", leftMargin, yPos);
    doc.line(leftMargin, yPos + 2, 70, yPos + 2);
    yPos += 10;

    doc.text("Tipo de Expediente", 25, yPos);
    doc.text(": " + (datos.tipo_expediente || ''), 60, yPos);
    yPos += 10;

    doc.text("Materia del Proceso", 25, yPos);
    doc.text(": " + limitarLineas(doc, datos.materia_proceso || '', 120, 2), 60, yPos, { align: "justify", maxWidth: 130 });
    yPos += 10;

    if (datos.demandante) {
      doc.text("Demandante", 25, yPos);
      doc.text(": " + limitarLineas(doc, datos.demandante, 120, 2), 60, yPos, { align: "justify", maxWidth: 130 });
      yPos += 10;
    }

    if (datos.demandado) {
      doc.text("Demandado", 25, yPos);
      doc.text(": " + limitarLineas(doc, datos.demandado, 120, 2), 60, yPos, { align: "justify", maxWidth: 130 });
      yPos += 10;
    }

    if (datos.causante) {
      doc.text("Causante", 25, yPos);
      doc.text(": " + limitarLineas(doc, datos.causante, 120, 2), 60, yPos, { align: "justify", maxWidth: 130 });
      yPos += 10;
    }

    doc.text("Juzgado", 25, yPos);
    doc.text(": " + limitarLineas(doc, datos.juzgado || '', 120, 2), 60, yPos, { align: "justify", maxWidth: 130 });
    yPos += 10;

    const mes = datos.mes ? datos.mes + ', ' : '';
    const dia = datos.dia ? datos.dia + ' de ' : '';
    doc.text("Lugar y Fecha", 25, yPos);
    doc.text(": " + (datos.ubigeo_soli || '') + "; " + dia + mes + (datos.anio || ''), 60, yPos, { align: "justify", maxWidth: 130 });
    yPos += 10;

    return yPos;
  }

  /**
   * Genera contenido específico para Ministerio Público
   */
  function generarContenidoMinisterioPublico(doc, datos, maxWidth, montoBusqueda, boletaPago, estadoPago) {
    const leftMargin = 20;
    let yPos = 80;

    doc.text("DATOS DEL EXPEDIENTE - MINISTERIO PÚBLICO:", leftMargin, yPos);
    doc.line(leftMargin, yPos + 2, 90, yPos + 2);
    yPos += 10;

    doc.text("Tipo de Expediente", 25, yPos);
    doc.text(": " + (datos.tipo_expediente_mp || ''), 60, yPos);
    yPos += 8;

    doc.text("Caso", 25, yPos);
    doc.text(": " + limitarLineas(doc, datos.caso_mp || '', 120, 2), 60, yPos, { align: "justify", maxWidth: 130 });
    yPos += 8;

    doc.text("Área", 25, yPos);
    doc.text(": " + (datos.area_mp || ''), 60, yPos);
    yPos += 8;

    doc.text("Materia", 25, yPos);
    doc.text(": " + (datos.materia_mp || ''), 60, yPos);
    yPos += 8;

    doc.text("Agraviado/Denunciante", 25, yPos);
    doc.text("    : " + limitarLineas(doc, datos.agraviado_mp || '', 120, 2), 60, yPos, { align: "justify", maxWidth: 130 });
    yPos += 8;

    doc.text("Imputado/Denunciado", 25, yPos);
    doc.text("    : " + limitarLineas(doc, datos.imputado_mp || '', 120, 2), 60, yPos, { align: "justify", maxWidth: 130 });
    yPos += 8;

    doc.text("Fiscalía", 25, yPos);
    doc.text(": " + limitarLineas(doc, datos.fiscalia_mp || '', 120, 2), 60, yPos, { align: "justify", maxWidth: 130 });
    yPos += 8;

    // 📌 Generar texto en una sola línea: "N° de Caso: -- ; N° de Paquete: --"
    const numeroCaso = datos.numero_caso_mp || '';
    const numeroPaquete = datos.numero_paquete_mp || '';

    // 🟩 Construir texto completo
    const textoCasoPaquete = `N° de Caso: ${numeroCaso}     ; N° de Paquete: ${numeroPaquete}`;

    // 🖨️ Imprimir en una sola línea
    doc.text(textoCasoPaquete, 25, yPos, {
      align: "justify",
      maxWidth: 170
    });

    // Espaciado inferior
    yPos += 10;

    const mes = datos.mes ? datos.mes + ', ' : '';
    const dia = datos.dia ? datos.dia + ' de ' : '';
    doc.text("Lugar y Fecha", 25, yPos);
    doc.text(": " + (datos.ubigeo_soli || '') + "; " + dia + mes + (datos.anio || ''), 60, yPos, { align: "justify", maxWidth: 130 });
    yPos += 10;

    return yPos;
  }

  /**
   * Genera contenido para otros trámites genéricos
   */
  function generarContenidoGenerico(doc, datos, maxWidth, montoBusqueda, boletaPago, estadoPago) {
    const leftMargin = 20;
    let yPos = 80;

    const tramiteLabels = {
      'enace': 'ENACE',
      'impuesto': 'IMPUESTO SUCESORIOS',
      'procesos': 'PROCESOS NO CONTENCIOSOS'
    };

    const labelTramite = tramiteLabels[datos.tipo_tramite] || datos.tipo_tramite?.toUpperCase() || 'TRÁMITE';

    doc.text(`DATOS DEL ${labelTramite}:`, leftMargin, yPos);
    doc.line(leftMargin, yPos + 2, 70, yPos + 2);
    yPos += 10;

    // Campos específicos para ENACE
    if (datos.tipo_tramite === 'enace') {
      if (datos.contrato_privado) {
        doc.text("Contrato Privado", 25, yPos);
        doc.text(": " + limitarLineas(doc, datos.contrato_privado, 120, 2), 60, yPos, { align: "justify", maxWidth: 130 });
        yPos += 10;
      }
      if (datos.otorgante_enace) {
        doc.text("Otorgante", 25, yPos);
        doc.text(": " + limitarLineas(doc, datos.otorgante_enace, 120, 2), 60, yPos, { align: "justify", maxWidth: 130 });
        yPos += 10;
      }
      if (datos.favorecido_enace) {
        doc.text("Favorecido", 25, yPos);
        doc.text(": " + limitarLineas(doc, datos.favorecido_enace, 120, 2), 60, yPos, { align: "justify", maxWidth: 130 });
        yPos += 10;
      }
      if (datos.institucion_enace) {
        doc.text("Institución", 25, yPos);
        doc.text(": " + limitarLineas(doc, datos.institucion_enace, 120, 2), 60, yPos, { align: "justify", maxWidth: 130 });
        yPos += 10;
      }
    }

    // Campos específicos para IMPUESTO
    if (datos.tipo_tramite === 'impuesto') {
      if (datos.causante_impuesto) {
        doc.text("Causante", 25, yPos);
        doc.text(": " + limitarLineas(doc, datos.causante_impuesto, 120, 2), 60, yPos, { align: "justify", maxWidth: 130 });
        yPos += 10;
      }
      if (datos.direccion_impuesto) {
        doc.text("Dirección del Bien", 25, yPos);
        doc.text(": " + limitarLineas(doc, datos.direccion_impuesto, 120, 2), 60, yPos, { align: "justify", maxWidth: 130 });
        yPos += 10;
      }
    }

    // Campos específicos para PROCESOS
    if (datos.tipo_tramite === 'procesos') {
      if (datos.proceso_de) {
        doc.text("Proceso de", 25, yPos);
        doc.text(": " + limitarLineas(doc, datos.proceso_de, 120, 2), 60, yPos, { align: "justify", maxWidth: 130 });
        yPos += 10;
      }
      if (datos.en_contra_de) {
        doc.text("En Contra de", 25, yPos);
        doc.text(": " + limitarLineas(doc, datos.en_contra_de, 120, 2), 60, yPos, { align: "justify", maxWidth: 130 });
        yPos += 10;
      }
      if (datos.causante_proceso) {
        doc.text("Causante", 25, yPos);
        doc.text(": " + limitarLineas(doc, datos.causante_proceso, 120, 2), 60, yPos, { align: "justify", maxWidth: 130 });
        yPos += 10;
      }
      if (datos.notario_proceso) {
        doc.text("Notario", 25, yPos);
        doc.text(": " + limitarLineas(doc, datos.notario_proceso, 120, 2), 60, yPos, {
          align: "justify",
          maxWidth: 130
        });
        yPos += 10;
      }
    }

    const mes = datos.mes ? datos.mes + ', ' : '';
    const dia = datos.dia ? datos.dia + ' de ' : '';
    doc.text("Lugar y Fecha", 25, yPos);
    doc.text(": " + (datos.ubigeo_soli || '') + "; " + dia + mes + (datos.anio || ''), 60, yPos, { align: "justify", maxWidth: 130 });
    yPos += 10;

    return yPos;
  }

  /**
   * Función principal para generar PDF según el tipo de trámite
   */
  async function generarPDF(datos) {
    loading.value = false;

    let montoBusqueda = '';
    let boletaPago = '';
    let estadoPago = '';

    if (datos.pago_busqueda) {
      const pago = await PagoService.get(datos.pago_busqueda);
      const pagoData = pago?.data || pago;
      montoBusqueda = pagoData?.total ? formatNumberToSoles(pagoData.total) : '';
      boletaPago = pagoData?.boleta_id ? pagoData.boleta_id : '';

      if (pagoData?.estado === 1) estadoPago = 'Pendiente';
      else if (pagoData?.estado === 0) estadoPago = 'Cancelado';
      else if (pagoData?.estado == null) estadoPago = 'Anulado';
      else estadoPago = '';
    }

    const nombrePDF = "solicitud_" + datos.id + ".pdf";
    const doc = new jsPDF("p", "mm", "a4");
    doc.setFont("times");
    doc.setFontSize(12);
    doc.setTextColor(0, 0, 0);

    const leftMargin = 20;
    const topMargin = 20;
    const maxWidth = doc.internal.pageSize.width - leftMargin * 2;

    // =============================================
    // ENCABEZADO COMÚN PARA TODOS LOS TRÁMITES
    // =============================================
    doc.addImage('src//assets/img/logo_ARP.png', 'PNG', 25, 10, 55, 0);
    doc.text(convertDate(datos?.created_at ? datos.created_at : new Date, "d 'de' MMMM, yyyy h:mm:ss a"), 120, 18);
    doc.setLineWidth(0.5);
    doc.line(20, 24, 190, 24);

    // doc.text("N° Solicitud: S-" + datos.id.toString().padStart(5, '0'), 120, 30);
    // Texto inicial en normal
    doc.setFont("times", "normal");
    doc.text("N° Solicitud: S-", 120, 30);

    // Calcular la posición exacta donde termina el texto anterior
    const offsetX = doc.getTextWidth("N° Solicitud: S-") + 120;

    // Texto del número en negrita
    doc.setFont("times", "bold");
    doc.setFontSize(14)
    doc.text(datos.solicitud_code.toString().padStart(5, '0'), offsetX, 30);

    // Restaurar fuente normal (opcional)
    doc.setFont("times", "normal");
    doc.setFontSize(12)

    const nombre_asunto = datos.tipo_documento === 'DNI' ? datos.nombre_completo : datos.asunto;
    const parrafo1 = `        Yo, ${nombre_asunto} natural de ${datos.ubigeo_pers} identificado con ${datos.tipo_documento} ${datos.num_documento} y con domicilio en ${datos.direccion} del distrito ${datos.ubigeo_pers}, ante Usted con el debido respeto me presento y expongo:`;
    doc.text(parrafo1, 20, 37, { align: "justify", maxWidth: maxWidth });
    doc.text("Celular: " + datos.celular + '\t' + "Correo: " + datos.correo, 30, 53);

    // =============================================
    // NUEVA SECCIÓN AGREGADA - TIPO DE COPIA
    // =============================================
    const parrafo2 = `        Que amparado en los Dispositivos Legales Vigentes, Solicito se me expida el documento de acuerdo al los siguientes detalles: `;
    doc.text(parrafo2, 20, 60, { align: "justify", maxWidth: maxWidth });

    // Cuadros de selección de tipo de copia
    doc.rect(25, 68, 8, 5);
    doc.text("Testimonio", 35, 72);

    doc.rect(60, 68, 8, 5);
    doc.text("Copia Certificada", 70, 72);

    doc.rect(105, 68, 8, 5);
    doc.text("Copia Simple", 115, 72);

    // Marcar la selección según el tipo de copia
    if(datos.tipo_copia==='0701')
      doc.text("X", 28, 72);
    else if(datos.tipo_copia==='0702')
      doc.text("X", 63, 72);
    else if(datos.tipo_copia==='0703')
      doc.text("X", 108, 72);

    doc.text("Otros:", 145, 72);
    doc.line(145, 78, 190, 78);

    // =============================================
    // CONTENIDO ESPECÍFICO SEGÚN TIPO DE TRÁMITE
    // =============================================
    let yPosFinal = 90; // Ajustado para dar espacio a la nueva sección

    switch (datos.tipo_tramite) {
      case 'escrituras':
        yPosFinal = generarContenidoEscrituras(doc, datos, maxWidth, montoBusqueda, boletaPago, estadoPago);
        break;
      case 'partidas':
        yPosFinal = generarContenidoPartidas(doc, datos, maxWidth, montoBusqueda, boletaPago, estadoPago);
        break;
      case 'expedientes':
        yPosFinal = generarContenidoExpedientes(doc, datos, maxWidth, montoBusqueda, boletaPago, estadoPago);
        break;
      case 'ministerio_publico':
        yPosFinal = generarContenidoMinisterioPublico(doc, datos, maxWidth, montoBusqueda, boletaPago, estadoPago);
        break;
      case 'enace':
      case 'impuesto':
      case 'procesos':
        yPosFinal = generarContenidoGenerico(doc, datos, maxWidth, montoBusqueda, boletaPago, estadoPago);
        break;
      default:
        // Para trámites no especificados
        doc.text("TRÁMITE SOLICITADO:", 20, yPosFinal);
        doc.line(20, yPosFinal + 2, 70, yPosFinal + 2);
        doc.text("Tipo de Trámite: " + (datos.tipo_tramite || ''), 25, yPosFinal + 10);
        yPosFinal = yPosFinal + 20;
    }

    // =============================================
    // OBSERVACIONES (COMÚN PARA TODOS)
    // =============================================
    if (datos.mas_datos) {
      doc.text("Observaciones", 25, yPosFinal);
      doc.text(": " + limitarLineas(doc, datos.mas_datos, 120, 3), 60, yPosFinal, { align: "justify", maxWidth: 130 });
      yPosFinal += 10;
    }

    // =============================================
    // FIRMA Y PAGO (COMÚN PARA TODOS)
    // =============================================
    doc.text("POR LO TANTO:  Ruego a Usted acceder a mi solicitud por ser justa y legal.", 20, yPosFinal + 5);

    // Firma del solicitante
    doc.line(40, yPosFinal + 20, 90, yPosFinal + 20);
    doc.text('FIRMA DEL SOLICITANTE', 40, yPosFinal + 24);

    // Cuadro de información de pago
    doc.setDrawColor(0, 0, 0);
    doc.setLineWidth(0.3);
    let x = 118;
    let y = yPosFinal + 10;
    let width = 78;
    let height = 10;
    doc.rect(x, y, width, height);

    doc.setFontSize(12);
    doc.setFont("times", "normal");
    doc.text('IMPORTE BUSQUEDA: ' + montoBusqueda, 120, y + 4);

    doc.setFontSize(9);
    doc.text('N° Boleta: ' + (boletaPago || '__________'), 120, y + 8);
    doc.text('Estado del pago: ' + (estadoPago || '__________'), 150, y + 8);

    doc.setFontSize(12);
    doc.text('Puno, ' + convertDate(datos?.created_at ? datos.created_at : new Date, "EEEE d 'de' MMMM y"), 120, yPosFinal + 24);

    // =============================================
    // SECCIONES DE BÚSQUEDA Y VERIFICACIÓN (COMÚN)
    // =============================================
    let ySecciones = yPosFinal + 35;

    // FASE DE BÚSQUEDA
    doc.text("FASE DE BUSQUEDA:", 20, ySecciones);
    doc.line(20, ySecciones + 2, 70, ySecciones + 2);
    doc.text("Buscado por: " + (datos?.nombreBuscador ? datos.nombreBuscador : '________________________________'), 25, ySecciones + 7);
    doc.text("Firma:__________________", 135, ySecciones + 7);
    doc.text("Protocolo: " + (datos?.cod_protocolo ? datos.cod_protocolo : '_______________'), 25, ySecciones + 14);
    doc.text("Registro N°: " + (datos?.id_busqueda ? datos.id_busqueda : '__________'), 80, ySecciones + 14);
    doc.text("Legajo N°:________", 135, ySecciones + 14);
    doc.text(datos.tipo_tramite === 'escrituras' ? "Escritura N°: " + (datos?.cod_escritura ? datos.cod_escritura : '_____________') : "Documento N°:______", 25, ySecciones + 21);
    doc.text("Folio, del: " + (datos?.cod_folioInicial ? datos.cod_folioInicial : '________'), 80, ySecciones + 21);
    doc.text("al: " + (datos?.cod_folioFinal ? datos.cod_folioFinal : '_________'), 135, ySecciones + 21);
    doc.text("Observaciones del Buscador: " + (datos?.observaciones ? datos.observaciones : '____________________________________________________________________________________________________________'), 25, ySecciones + 28, { align: "justify", maxWidth: maxWidth - 10 });

    // FASE DE VERIFICACIÓN
    ySecciones += 42;
    doc.text("FASE DE VERIFICACIÓN:", 20, ySecciones);
    doc.line(20, ySecciones + 2, 70, ySecciones + 2);
    doc.text("Verificado por: " + (datos?.nombreVerificad ? datos.nombreVerificad : '______________________________'), 25, ySecciones + 7);
    doc.text("Firma:_________________", 135, ySecciones + 7);

    // OBSERVACIONES DEL VERIFICADOR - AGREGADO
    doc.text("Observaciones del Verificador: " + (datos?.observacionesVeri ? datos.observacionesVeri : '____________________________________________________________________________________________________________'), 25, ySecciones + 14, { align: "justify", maxWidth: maxWidth - 10 });

    // =============================================
    // PIE DE PÁGINA (COMÚN PARA TODOS)
    // =============================================
    const totalPages = doc.internal.getNumberOfPages();
    for (let i = 1; i <= totalPages; i++) {
      doc.setPage(i);
      const pageHeight = doc.internal.pageSize.height;
      const marginBottom = 10;

      doc.setFontSize(9);
      doc.setTextColor(0, 0, 0);
      doc.text("Archivo Regional de Puno - ", 20, pageHeight - marginBottom + 4);

      doc.setFontSize(9);
      let startX = 20;
      let startY = pageHeight - marginBottom - 5;

      doc.text("Esta es una representación impresa cuya autenticidad puede ser contrastada con la representación imprimible localizada en la ", startX, startY);
      doc.text("sede digital del Gobierno Regional, aplicando lo dispuesto por el Art. 25 de D.S. 070–2013-PCM y la Tercera Disposición", startX, startY + 3);
      doc.text("Complementaria Final del D.S. 026-2016-PCM. Su autenticidad e integridad pueden ser contrastadas a través de la siguiente :", startX, startY + 6);
      doc.text("", startX, startY + 9);

      const codigoSolicitud = "S-" + datos.id.toString().padStart(5, '0');
      const urlSeguimiento = `www.archivoregional.gob.pe/reguimiento?codigo=${codigoSolicitud}`;
      doc.setFontSize(9);
      doc.text(`CONSULTA DE SEGUIMIENTO: ${urlSeguimiento}`, 56, pageHeight - marginBottom + 4);

      doc.setLineWidth(1);
      let xLine = 183;
      let yStart = pageHeight - 18;
      let yEnd = yStart + 13;
      doc.line(xLine, yStart, xLine, yEnd);

      const qrCanvas = document.createElement("canvas");
      await QRCode.toCanvas(qrCanvas, urlSeguimiento, { width: 20, margin: 0 });
      const qrDataURL = qrCanvas.toDataURL("image/png");
      doc.addImage(qrDataURL, "PNG", 185, pageHeight - 18, 13, 13);
    }

    // =============================================
    // SALIDA DEL PDF
    // =============================================
    if (props.ver)
      data.value = URL.createObjectURL(new Blob([doc.output("blob")], { type: "application/pdf" }));
    else
      window.open(doc.output("bloburl"), "_blank");
  }

  // =============================================
  // FUNCIONES AUXILIARES (MANTENIDAS)
  // =============================================

  async function VerificaDatos(){
    let datosEnPDF = null;
    if(props.datosSolicitudRow){
      const listMes = ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Setiembre', 'Octubre', 'Noviembre', 'Diciembre'];

      datosEnPDF = {
        // Datos del solicitante
        nombres: props.datosSolicitudRow.solicitante? props.datosSolicitudRow.solicitante.nombres : '',
        apellido_paterno: props.datosSolicitudRow.solicitante? props.datosSolicitudRow.solicitante.apellido_paterno : '',
        apellido_materno: props.datosSolicitudRow.solicitante? props.datosSolicitudRow.solicitante.apellido_materno : '',
        nombre_completo: props.datosSolicitudRow.solicitante? props.datosSolicitudRow.solicitante.nombre_completo : '',
        asunto: props.datosSolicitudRow.solicitante? props.datosSolicitudRow.solicitante.asunto : '',
        num_documento: props.datosSolicitudRow.solicitante? props.datosSolicitudRow.solicitante.num_documento : '',
        tipo_documento: props.datosSolicitudRow.solicitante? props.datosSolicitudRow.solicitante.tipo_documento : '',
        direccion: props.datosSolicitudRow.solicitante? props.datosSolicitudRow.solicitante.direccion : '',
        correo: props.datosSolicitudRow.solicitante? props.datosSolicitudRow.solicitante.correo : '',
        celular: props.datosSolicitudRow.solicitante? props.datosSolicitudRow.solicitante.celular : '',
        ubigeo_pers: props.datosSolicitudRow.solicitante? props.datosSolicitudRow.solicitante.ubigeo? props.datosSolicitudRow.solicitante.ubigeo.nombre : '' : '',

        // Datos de la solicitud
        id: props.datosSolicitudRow.id,
        tipo_tramite: props.datosSolicitudRow.tipo_tramite,
        tipo_copia: props.datosSolicitudRow.tipo_copia, // IMPORTANTE: Agregado para la nueva sección

        // Campos específicos por tipo de trámite
        // Escrituras
        otorgantes: props.datosSolicitudRow.otorgantes,
        favorecidos: props.datosSolicitudRow.favorecidos,
        anio: props.datosSolicitudRow.anio,
        mes: props.datosSolicitudRow.mes? listMes[parseInt(props.datosSolicitudRow.mes)-1] : null,
        dia: props.datosSolicitudRow.dia,
        ubigeo_soli: props.datosSolicitudRow?.ubigeo? props.datosSolicitudRow.ubigeo.nombre : '',
        // CAMBIO: Agregar municipalidad según código UBIGEO
        municipalidad_ubigeo: props.datosSolicitudRow?.ubigeo? props.datosSolicitudRow.ubigeo.municipalidad || props.datosSolicitudRow.ubigeo.nombre : '',
        bien: props.datosSolicitudRow.bien,
        mas_datos: props.datosSolicitudRow.mas_datos,
        notario: props.datosSolicitudRow.notario? props.datosSolicitudRow.notario.nombre_completo : '',
        sub_serie: props.datosSolicitudRow.sub_serie? props.datosSolicitudRow.sub_serie.nombre : '',

        // Partidas
        tipo_partida: props.datosSolicitudRow.tipo_partida,
        nombre_fallecido: props.datosSolicitudRow.nombre_fallecido,
        nombre_nacido: props.datosSolicitudRow.nombre_nacido,
        nombre_esposo: props.datosSolicitudRow.nombre_esposo,
        nombre_esposa: props.datosSolicitudRow.nombre_esposa,

        // Expedientes
        tipo_expediente: props.datosSolicitudRow.tipo_expediente,
        materia_proceso: props.datosSolicitudRow.materia_proceso,
        demandante: props.datosSolicitudRow.demandante,
        demandado: props.datosSolicitudRow.demandado,
        causante: props.datosSolicitudRow.causante,
        juzgado: props.datosSolicitudRow.juzgado,
        secretario: props.datosSolicitudRow.secretario,

        // Ministerio Público
        tipo_expediente_mp: props.datosSolicitudRow.tipo_expediente_mp,
        caso_mp: props.datosSolicitudRow.caso_mp,
        area_mp: props.datosSolicitudRow.area_mp,
        materia_mp: props.datosSolicitudRow.materia_mp,
        agraviado_mp: props.datosSolicitudRow.agraviado_mp,
        imputado_mp: props.datosSolicitudRow.imputado_mp,
        fiscalia_mp: props.datosSolicitudRow.fiscalia_mp,
        numero_caso_mp: props.datosSolicitudRow.numero_caso_mp,
        numero_paquete_mp: props.datosSolicitudRow.numero_paquete_mp,

        // ENACE
        contrato_privado: props.datosSolicitudRow.contrato_privado,
        otorgante_enace: props.datosSolicitudRow.otorgante_enace,
        favorecido_enace: props.datosSolicitudRow.favorecido_enace,
        institucion_enace: props.datosSolicitudRow.institucion_enace,

        // Impuesto
        causante_impuesto: props.datosSolicitudRow.causante_impuesto,
        direccion_impuesto: props.datosSolicitudRow.direccion_impuesto,

        // Procesos
        proceso_de: props.datosSolicitudRow.proceso_de,
        en_contra_de: props.datosSolicitudRow.en_contra_de,
        causante_proceso: props.datosSolicitudRow.causante_proceso,
        notario_proceso: props.datosSolicitudRow.notario_proceso ? props.datosSolicitudRow.notario_proceso.nombre_completo : '',

        // Información general
        tipo_copia: props.datosSolicitudRow.tipo_copia,
        cantidad_copia: props.datosSolicitudRow.cantidad_copia,
        created_at: props.datosSolicitudRow.created_at,
        pago_busqueda: props.datosSolicitudRow.pago_busqueda,
      };
    }

    // Agregar datos de búsqueda, verificación y pagos si están disponibles
    if(props.datosBusqueda){
      datosEnPDF.nombreBuscador = props.datosBusqueda.user? props.datosBusqueda.user.name : '';
      datosEnPDF.cod_protocolo = props.datosBusqueda.cod_protocolo;
      datosEnPDF.id_busqueda = props.datosBusqueda.id;
      datosEnPDF.cod_escritura = props.datosBusqueda.cod_escritura;
      datosEnPDF.cod_folioInicial = props.datosBusqueda.cod_folioInicial;
      datosEnPDF.cod_folioFinal = props.datosBusqueda.cod_folioFinal;
      datosEnPDF.observaciones = props.datosBusqueda.observaciones;
    }

    if(props.datosVerificacion){
      datosEnPDF.nombreVerificad = props.datosVerificacion.user? props.datosVerificacion.user.name : '';
      datosEnPDF.observacionesVeri = props.datosVerificacion.observaciones; // OBSERVACIONES DEL VERIFICADOR
    }

    if(props.datosPagos){
      datosEnPDF.pago_verificacion = props.datosPagos.pago_verificacion;
      datosEnPDF.cantidad_folio = props.datosPagos.cantidad_folio;
      datosEnPDF.pago_folio = props.datosPagos.pago_folio;
      datosEnPDF.cantidad_hojas = props.datosPagos.cantidad_fotocopia * props.datosPagos.cantidad_folio;
      datosEnPDF.pago_fotocopia = props.datosPagos.pago_fotocopia;
    }

    if(datosEnPDF)
      await generarPDF(datosEnPDF);
  }

  class eventos{
    activarCargar(){
      loading.value = true;
    }
    verificacion(){
      VerificaDatos();
    }
  }

  function Evento(){
    emits("clickPDF",new eventos());
  }

  function descargarPDF() {
    window.htm12canvas = html2canvas;
    let doc = new jsPDF("p", "pt", "a4");
    doc.html(document.querySelector("#documento"), {
      callback: function (pdf) {
        window.open(pdf.output("bloburl"), "_blank");
      }
    });
  }
  </script>

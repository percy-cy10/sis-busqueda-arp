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
import jsPDF from "jspdf";
import { convertDate } from "src/utils/ConvertDate";
import { formatNumberToSoles } from "src/utils/ConvertMoney";
import { useQuasar } from "quasar";
import { onMounted, ref } from "vue";
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
onMounted(()=>{
  if (props.ver) {
    VerificaDatos();
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
function generarPDF(datos) {
  loading.value = false;
  const nombrePDF = "mi_pdf_nombre.pdf";
  // Crear un nuevo documento PDF
  const doc = new jsPDF("p", "mm", "a4");
  // Configurar el formato y estilo
  doc.setFont("times");
  doc.setFontSize(12);
  doc.setTextColor(0, 0, 0); // Color de texto: negro

  const leftMargin = 20;
  const topMargin = 20;

  const maxWidth = doc.internal.pageSize.width - leftMargin  * 2;

  // Agregar contenido al PDF con formato
  doc.addImage('src//assets/img/logo_ARP.png', 'PNG', 25, 10, 55, 0);
  // doc.text("ARCHIVO REGIONAL DE LA NACION", 25, 12);
  // doc.text("Archivo Regional de Puno", 38, 18);
  doc.text(convertDate(datos?.created_at ?datos.created_at:new Date,"d 'de' MMMM, yyyy h:mm:ss a"), 120, 18);
  doc.setLineWidth(0.5);
  doc.line(20, 24, 190, 24); // Línea separadora

  doc.text("N° Solicitud: S-"+datos.id.toString().padStart(5, '0'), 120, 30);
  const nombre_asunto = datos.tipo_documento==='DNI'? datos.nombre_completo : datos.asunto;
  const parrafo1 = `        Yo, ${ nombre_asunto } natural de ${ datos.ubigeo_pers } identificado con ${datos.tipo_documento} ${ datos.num_documento } y con domicilio en ${ datos.direccion } del distrito ${ datos.ubigeo_pers }, ante Usted con el debido respeto me presento y expongo:`;
  // const lineas = doc.splitTextToSize(parrafo1, maxWidth);
  doc.text(parrafo1, 20, 40, { align: "justify" , maxWidth: maxWidth});
  doc.text("Celular: "+datos.celular+'\t'+"Correo: "+datos.correo, 30, 60);

  const parrafo2 = `        Que amparado en los Dispositivos Legales Vigentes, Solicito se me expida el documento de acuerdo al los siguientes detalles: `;
  doc.text(parrafo2, 20, 67,{ align: "justify" , maxWidth: maxWidth});

  doc.rect(25, 76, 8, 5); doc.text("Testimonio", 35, 80);
  doc.rect(60, 76, 8, 5); doc.text("Copia Certificada", 70, 80);
  doc.rect(105, 76, 8, 5);  doc.text("Copia Simple", 115, 80);
  if(datos.tipo_copia==='0701')
    doc.text("X", 28, 80);
  else if(datos.tipo_copia==='0702')
    doc.text("X", 63, 80);
  else if(datos.tipo_copia==='0703')
    doc.text("X", 108, 80);

  doc.text("Otros:", 145, 77);
  doc.line(145, 83, 190, 83);

  doc.text("DATOS DEL DOCUMENTO:", 20, 90);
  doc.line(20, 92, 70, 92);
  const lineas = doc.splitTextToSize(parrafo1, maxWidth);
  doc.text("Escritura Pública", 25, 98);  doc.text(": "+limitarLineas(doc,datos.subserie,120,2), 60, 98,{ align: "justify" , maxWidth: 130});
  doc.text("Otorgado por", 25, 108);      doc.text(": "+limitarLineas(doc,datos.otorgantes,120,2), 60, 108,{ align: "justify" , maxWidth: 130});
  doc.text("A Favor de", 25, 118);        doc.text(": "+limitarLineas(doc,datos.favorecidos ,120,2), 60, 118,{ align: "justify" , maxWidth: 130});
  doc.text("Notario Público", 25, 128);   doc.text(": "+limitarLineas(doc,datos.notario ,120,2), 60, 128,{ align: "justify" , maxWidth: 130});
  const mes = datos.mes?datos.mes+', ':'';
  const dia = datos.dia?datos.dia+' de ':'';
  doc.text("Lugar y Fecha", 25, 138);     doc.text(": "+datos.ubigeo_soli+"; "+dia+mes+datos.anio, 60, 138,{ align: "justify" , maxWidth: 130});
  doc.text("Nombre del Bien", 25, 145);   doc.text(": "+limitarLineas(doc,datos.bien ,120,2), 60, 145,{ align: "justify" , maxWidth: 130});
  doc.text("Otros", 25, 155);             doc.text(": "+limitarLineas(doc,datos.mas_datos,120,2), 60, 155,{ align: "justify" , maxWidth: 130});
  doc.text("POR LO TANTO:  Ruego a Usted acceder a mi solicitud por ser justa y legal.", 20, 165);

  doc.line(40, 180, 90, 180); // firma del solicitante
  doc.text('FIRMA DEL SOLICITANTE', 40, 184);
  doc.text('IMPORTE BUSQUEDA: '+formatNumberToSoles(datos.pago_busqueda), 120, 175);
  doc.text('Puno, '+convertDate(datos?.created_at?datos.created_at:new Date,"EEEE d 'de' MMMM y"), 120, 183);
  /*** SECCION DE BUSQUEDA ****************************************************************************************************************************************************************************************** */
  doc.text("FASE DE BUSQUEDA:", 20, 190);
  doc.line(20, 192, 70, 192);
  doc.text("Buscado por: "+(datos?.nombreBuscador?datos.nombreBuscador:'________________________________'), 25, 197);    doc.text("Firma:__________________", 135, 197);
  doc.text("Protocolo: "+(datos?.cod_protocolo?datos.cod_protocolo:'_______________'), 25, 204);    doc.text("Registro N°: "+(datos?.id_busqueda?datos.id_busqueda:'__________'), 80, 204);    doc.text("Legajo N°:________", 135, 204);
  doc.text(true?"Escritura N°: "+(datos?.cod_escritura?datos.cod_escritura:'_____________'):"Minuta N°:______", 25, 211);   doc.text("Folio, del: "+(datos?.cod_folioInicial?datos.cod_folioInicial:'________'), 80, 211);    doc.text("al: "+(datos?.cod_folioFinal?datos.cod_folioFinal:'_________'), 135, 211);
  doc.text("Observaciones del Buscador: "+(datos?.observaciones?datos.observaciones:'____________________________________________________________________________________________________________'), 25, 218,{align: "justify" , maxWidth: maxWidth-10});
  /***** SECCION DE VERIFICACIÓN *********************************************************************************************************************************************************************************** */
  doc.text("FASE DE VERIFICACIÓN:", 20, 232);
  doc.line(20, 234, 70, 234);
  doc.text("Verificado por: "+(datos?.nombreVerificad?datos.nombreVerificad:'______________________________'), 25, 241);    doc.text("Firma:_________________", 135, 241);
  /*** SECCION DE cAJA ********************************************************************************************************************************************************* */
  doc.text("Derecho N° Copias:________", 25, 248);    doc.text("N° de hojas: "+(datos?.cantidad_folio?datos.cantidad_folio:'_____________'), 80, 248);    doc.text("s/: "+(datos?.pago_folio?datos.pago_folio:'_________'), 135, 248);
  doc.text("Verificación:_________________________________", 25, 255);    doc.text("s/: "+(datos?.pago_verificacion?datos.pago_verificacion:'_____________'), 135, 255);
  doc.text("N° de copias: "+(datos?.cantidad_hojas?datos.cantidad_hojas:'_____________'), 80, 262);    doc.text("s/: "+(datos?.pago_fotocopia?datos.pago_fotocopia:'_________'), 135, 262);
  doc.text("Observaciones: "+(datos?.observacionesVeri?datos.observacionesVeri:'____________________________________________________________________________________________________________'), 25, 269,{align: "justify" , maxWidth: maxWidth-10});
  if (props.ver)
    data.value = URL.createObjectURL(new Blob([doc.output("blob")], { type: "application/pdf" })); //doc.output("datauristring");
  else
    window.open(doc.output("bloburl"), "_blank");
}

function VerificaDatos(){
  let datosEnPDF = null;
  if(props.datosSolicitudRow){
    const listMes = ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Setiembre', 'Octubre', 'Noviembre', 'Diciembre'];
    // console.log(props.datosSolicitudRow);
    datosEnPDF = {
      nombres: props.datosSolicitudRow.solicitante?props.datosSolicitudRow.solicitante.nombres:'',
      apellido_paterno: props.datosSolicitudRow.solicitante?props.datosSolicitudRow.solicitante.apellido_paterno:'',
      apellido_materno: props.datosSolicitudRow.solicitante?props.datosSolicitudRow.solicitante.apellido_materno:'',
      nombre_completo: props.datosSolicitudRow.solicitante?props.datosSolicitudRow.solicitante.nombre_completo:'',
      asunto: props.datosSolicitudRow.solicitante?props.datosSolicitudRow.solicitante.asunto:'',
      num_documento: props.datosSolicitudRow.solicitante?props.datosSolicitudRow.solicitante.num_documento:'',
      tipo_documento: props.datosSolicitudRow.solicitante?props.datosSolicitudRow.solicitante.tipo_documento:'',
      direccion: props.datosSolicitudRow.solicitante?props.datosSolicitudRow.solicitante.direccion:'',
      correo: props.datosSolicitudRow.solicitante?props.datosSolicitudRow.solicitante.correo:'',
      celular: props.datosSolicitudRow.solicitante?props.datosSolicitudRow.solicitante.celular:'',
      ubigeo_pers:props.datosSolicitudRow.solicitante?props.datosSolicitudRow.solicitante.ubigeo?props.datosSolicitudRow.solicitante.ubigeo.nombre:'':'',

      id: props.datosSolicitudRow.id,
      otorgantes: props.datosSolicitudRow.otorgantes,
      favorecidos: props.datosSolicitudRow.favorecidos,
      anio:props.datosSolicitudRow.anio,
      mes:props.datosSolicitudRow.mes?listMes[parseInt(props.datosSolicitudRow.mes)-1]:null,
      dia:props.datosSolicitudRow.dia,
      // fecha:props.datosSolicitudRow.fecha,
      ubigeo_soli: props.datosSolicitudRow?.ubigeo? props.datosSolicitudRow.ubigeo.nombre:'',
      bien: props.datosSolicitudRow.bien,
      mas_datos: props.datosSolicitudRow.mas_datos,
      notario:props.datosSolicitudRow.notario?props.datosSolicitudRow.notario.nombre_completo:'',
      subserie:props.datosSolicitudRow.subserie?props.datosSolicitudRow.subserie.nombre:'',
      tipo_copia:props.datosSolicitudRow.tipo_copia,
      cantidad_copia:props.datosSolicitudRow.cantidad_copia,
      created_at:props.datosSolicitudRow.created_at,
      pago_busqueda:props.datosSolicitudRow.pago_busqueda,
    };
    
  }
  if(props.datosBusqueda){
    datosEnPDF.nombreBuscador=props.datosBusqueda.user?props.datosBusqueda.user.name:'';
    datosEnPDF.cod_protocolo=props.datosBusqueda.cod_protocolo;
    datosEnPDF.id_busqueda=props.datosBusqueda.id;
    datosEnPDF.cod_escritura=props.datosBusqueda.cod_escritura;
    datosEnPDF.cod_folioInicial=props.datosBusqueda.cod_folioInicial;
    datosEnPDF.cod_folioFinal=props.datosBusqueda.cod_folioFinal;
    datosEnPDF.observaciones=props.datosBusqueda.observaciones;
  }
  if(props.datosVerificacion){
    datosEnPDF.nombreVerificad=props.datosVerificacion.user?props.datosVerificacion.user.name:'';
    datosEnPDF.observacionesVeri=props.datosVerificacion.observaciones;
  }
  if(props.datosPagos){
    console.log(props.datosPagos);
    datosEnPDF.pago_verificacion = props.datosPagos.pago_verificacion;
    datosEnPDF.cantidad_folio = props.datosPagos.cantidad_folio;
    datosEnPDF.pago_folio = props.datosPagos.pago_folio;
    datosEnPDF.cantidad_hojas = props.datosPagos.cantidad_fotocopia * props.datosPagos.cantidad_folio;
    datosEnPDF.pago_fotocopia = props.datosPagos.pago_fotocopia;
  }
  if(datosEnPDF)
    generarPDF(datosEnPDF);
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
      // pdf.save("mypdf.pdf");
      window.open(pdf.output("bloburl"), "_blank");
    }
  });
}
// doc.save(nombrePDF);
</script>

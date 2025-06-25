<script setup>
// Importaciones principales
import { ref, defineExpose } from 'vue'
import jsPDF from 'jspdf'
import QRCode from 'qrcode'
import logoArp from 'src/assets/img/logo_ARP.png'

// Utilidad para convertir imagen a base64
const getBase64Image = (url) => {
  return new Promise((resolve) => {
    const img = new window.Image()
    img.crossOrigin = 'Anonymous'
    img.onload = function () {
      const canvas = document.createElement('canvas')
      canvas.width = img.width
      canvas.height = img.height
      const ctx = canvas.getContext('2d')
      ctx.drawImage(img, 0, 0)
      resolve(canvas.toDataURL('image/png'))
    }
    img.src = url
  })
}

// Utilidad para convertir número a letras (simple)
function numeroALetras(num) {
  const unidades = ['','UNO','DOS','TRES','CUATRO','CINCO','SEIS','SIETE','OCHO','NUEVE']
  const decenas = ['','DIEZ','VEINTE','TREINTA','CUARENTA','CINCUENTA','SESENTA','SETENTA','OCHENTA','NOVENTA']
  const especiales = ['DIEZ','ONCE','DOCE','TRECE','CATORCE','QUINCE','DIECISÉIS','DIECISIETE','DIECIOCHO','DIECINUEVE']
  const centenas = ['','CIENTO','DOSCIENTOS','TRESCIENTOS','CUATROCIENTOS','QUINIENTOS','SEISCIENTOS','SETECIENTOS','OCHOCIENTOS','NOVECIENTOS']

  function convertir(n) {
    if (n === 0) return 'CERO'
    if (n === 100) return 'CIEN'
    let texto = ''
    if (n > 99) {
      texto += centenas[Math.floor(n/100)] + ' '
      n = n % 100
    }
    if (n > 19) {
      texto += decenas[Math.floor(n/10)]
      if (n % 10 > 0) texto += ' Y ' + unidades[n%10]
    } else if (n >= 10) {
      texto += especiales[n-10]
    } else if (n > 0) {
      texto += unidades[n]
    }
    return texto.trim()
  }

  let [entero, decimal] = num.toFixed(2).split('.')
  entero = parseInt(entero)
  decimal = parseInt(decimal)
  let texto = ''
  if (entero >= 1000000) return '***'
  if (entero >= 1000) {
    if (Math.floor(entero/1000) === 1) {
      texto += 'MIL '
    } else {
      texto += convertir(Math.floor(entero/1000)) + ' MIL '
    }
    entero = entero % 1000
  }
  texto += convertir(entero)
  texto = texto.trim() || 'CERO'
  return `${texto} CON ${decimal.toString().padStart(2,'0')}/100 SOLES`
}

// Calcula el subtotal de un tupa
function calcularSubtotalTupa(tupa) {
  if (tupa.Subtotal || tupa.pivot?.Subtotal) {
    return Number(tupa.Subtotal || tupa.pivot?.Subtotal).toFixed(2)
  }
  const cantidad = Number(tupa.cantidad || tupa.pivot?.cantidad || 1)
  const precio = Number(tupa.precio || tupa.costo || 0)
  return (cantidad * precio).toFixed(2)
}

// --- Función principal para generar el PDF ---
async function generarPDFPago(pago) {
  const tupas = Array.isArray(pago.tupas) ? pago.tupas : (pago.items || [])
  const subtotal = tupas.reduce((acc, tupa) => acc + Number(calcularSubtotalTupa(tupa)), 0)
  const igv = 0 // Cambia a subtotal * 0.18 si necesitas IGV
  const total = subtotal + igv
  const qrData = {
    Numero_Boleta: pago.id ? pago.id.toString().padStart(8, '0') : '--------',
    Nombre_Completo: pago.nombre_completo,
    Total: total,
    Fecha: pago.fecha_pago || pago.created_at || (new Date()).toLocaleString(),
    Atendido_por: pago.user?.name,
    Contacto: pago.user?.email
  }

  const doc = new jsPDF('p', 'mm', [80, 215 + tupas.length * 8])
  doc.setFont('times')
  doc.setFontSize(11)

  let y = 10

  // Logo
  const logoBase64 = await getBase64Image(logoArp)
  doc.addImage(logoBase64, 'PNG', 15, y, 50, 15)
  y += 23

  // Encabezado
  doc.setFont('times', 'bold')
  doc.text('GOBIERNO REGIONAL PUNO', 40, y, { align: 'center' }); y += 5
   doc.setFontSize(9)
  doc.setFont('times', 'normal')
  doc.text('RUC: 20406325815', 40, y, { align: 'center' }); y += 5
  doc.text('Dirección: Av. El Ejercito 645 - Barrio Chanu Chanu - Puno - Perú - 21001', 40, y, { align: 'center', maxWidth: 70 }); y += 8
  doc.text('D. L. N° 19414 Y Ley N° 25323', 40, y, { align: 'center', maxWidth: 70 }); y += 5
  doc.text('Teléfono: (051) 600704', 40, y, { align: 'center', maxWidth: 70 }); y += 8
  doc.setFont('times', 'bold')     // Negrita
  doc.setFontSize(14)              // Tamaño más grande (ajusta según necesites)
  doc.text('RECIBO ELECTRÓNICO', 40, y, { align: 'center' }); y += 8
  doc.setFont('times', 'normal')   // Volver a fuente normal
  doc.setFontSize(12)              // Volver al tamaño normal
  doc.text(`N°: ${pago.id ? pago.id.toString().padStart(8, '0') : '--------'}`, 40, y, { align: 'center' }); y += 8

  // Datos del pago
  doc.setFontSize(10)
  doc.setFont('times', 'bold')
  doc.text('N° Solicitud:', 5, y)

  doc.setFont('times', 'normal')
  doc.text(`${pago.solicitud?.id || ''}-${pago.solicitud?.bien || ''}`, 25, y) // Ajusta la posición "38" si es necesario

  y += 5

  // doc.text(`N° Solicitud: ${pago.solicitud?.id || ''}-${pago.solicitud?.bien || ''}`, 5, y); y += 5
  // Imprimir Nombre Completo del Solicitante
  doc.setFont('times', 'bold')
  doc.text('Nombre Completo:', 5, y)
  doc.setFont('times', 'normal')

  // Ajustar nombre completo al ancho máximo de 43mm
  const razonSocialLines = doc.splitTextToSize(pago.nombre_completo || '', 43)
  razonSocialLines.forEach((linea, index) => {
    doc.text(linea, 35, y + (index * 5)) // Alineado a partir de x = 35mm
  })

  y += razonSocialLines.length * 5 // Ajustar y al total de líneas escritas

  doc.setFont('times', 'bold')
  doc.text('Tipo Doc:', 5, y)
  doc.setFont('times', 'normal')
  doc.text(`${pago.tipo_documento || ''} : ${pago.num_documento || ''}`, 22, y)
  y += 5

  doc.setFont('times', 'bold')
  doc.text('Fecha Emisión:', 5, y)
  doc.setFont('times', 'normal')
  doc.text(`${pago.fecha_pago || (new Date()).toLocaleString()}`, 30, y)
  y += 7

  // Tabla de items
  doc.setFontSize(9)
  doc.setFont('times', 'bold')
  doc.text('Cant.', 6, y)
  doc.text('Descripción', 15, y)
  doc.text('V. Unid.', 50, y)
  doc.text('V. Total', 65, y)
  y += 2
  doc.line(5, y, 75, y); y += 5
  doc.setFont('times', 'normal')

  tupas.forEach(tupa => {
    const cantidad = String(tupa.cantidad || tupa.pivot?.cantidad || 1)
    const descripcion = (tupa.denominacion || tupa.label || '')
    const precio = Number(tupa.precio || tupa.costo || 0).toFixed(2)
    const subtotalTupa = calcularSubtotalTupa(tupa)

    // Divide la descripción si es muy larga (máximo 32mm de ancho)
    const descLines = doc.splitTextToSize(descripcion, 32)
    const maxLines = Math.max(descLines.length, 1)

    // Imprime la cantidad, precio y subtotal alineados a la derecha
    for (let i = 0; i < maxLines; i++) {
      doc.text(i === 0 ? cantidad : '', 5, y, { align: 'left', maxWidth: 4 })
      doc.text(descLines[i] || '', 15, y)
      doc.text(i === 0 ? precio : '', 50, y, { align: 'left', maxWidth: 15 })
      doc.text(i === 0 ? subtotalTupa : '', 75, y, { align: 'right', maxWidth: 15 })
      y += 4
    }
  })

  doc.line(5, y, 75, y); y += 5

  // y += 2

  doc.setFontSize(10)

  // Gravadas
  doc.setFont('times', 'bold')
  doc.text('Gravadas:', 53, y, { align: 'right' })
  doc.setFont('times', 'normal')
  doc.text(`S/ ${subtotal.toFixed(2)}`, 75, y, { align: 'right' })
  y += 5

  // IGV
  doc.setFont('times', 'bold')
  doc.text('IGV:',  53, y, { align: 'right' })
  doc.setFont('times', 'normal')
  doc.text(`S/ ${igv.toFixed(2)}`, 75, y, { align: 'right' })
  y += 5

  // Precio Venta
  doc.setFont('times', 'bold')

  doc.text('Importe Total:', 53, y, { align: 'right' })
  doc.setFontSize(12)
  doc.setFont('times', 'bold')
  doc.text(`S/ ${total.toFixed(2)}`, 75, y, { align: 'right' })
  y += 7

  // SON
  doc.setFontSize(10)
  doc.setFont('times', 'bold')
  doc.text('SON:', 5, y)
  doc.setFont('times', 'normal')
  const letras = doc.splitTextToSize(`${numeroALetras(total)}`, 60)
  letras.forEach((linea, i) => {
    doc.text(linea, 15, y + (i * 5))
  })
  y += letras.length * 5

  // NUEVO: Forma de pago y Condición
  doc.setFont('times', 'bold')
  doc.text('Forma de Pago:', 5, y)
  doc.setFont('times', 'normal')
  doc.text(`${pago.forma_pago || 'EFECTIVO'}`, 30, y)
  y += 5

  doc.setFont('times', 'bold')
  doc.text('Cond. de Pago :', 5, y)
  doc.setFont('times', 'normal')
  doc.text(`${pago.condicion_pago || 'AL CONTADO'}`, 30, y)
  y += 7

  // QR
  const qrDataUrl = await QRCode.toDataURL(JSON.stringify(qrData), { width: 80, margin: 1 })
  doc.addImage(qrDataUrl, 'PNG', 27.5, y, 25, 25)
  y += 32



 // Línea divisoria
  doc.setDrawColor(0)
  doc.line(5, y, 75, y)
  y += 5

  // Título del bloque
  doc.setFontSize(9)
  doc.setFont('times', 'bold')
  doc.text('EMISOR DE LA BOLETA', 40, y, { align: 'center' })
  y += 4

  // Detalle del usuario
  doc.setFont('times', 'bold')
  doc.text('Atendido por:', 5, y)
  doc.setFont('times', 'normal')
  doc.text(`${pago.user?.id || ''} - ${pago.user?.name || ''}`, 35, y)
  y += 4

  doc.setFont('times', 'bold')
  doc.text('Contacto:', 5, y)
  doc.setFont('times', 'normal')
  doc.text(`${pago.user?.email || ''}`, 35, y)
  y += 4

  doc.setFont('times', 'bold')
  doc.text('Fecha:', 5, y)
  doc.setFont('times', 'normal')
  doc.text(`${pago.user?.created_at || (new Date()).toLocaleDateString()}`, 35, y)
  y += 4

  // Línea divisoria
  doc.setDrawColor(0)
  doc.line(5, y, 75, y)
  y += 5



  // Pie de página
  doc.setFontSize(9)
  doc.text('Representación impresa de la RECIBO ELECTRÓNICO', 40, y, { align: 'center' })

  // Mostrar en nueva pestaña (no descargar)
  const pdfBlob = doc.output('blob');
  const pdfUrl = URL.createObjectURL(pdfBlob);
  window.open(pdfUrl, '_blank');
}

defineExpose({ generarPDFPago })
</script>

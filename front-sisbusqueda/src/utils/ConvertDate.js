import { parse, format, isValid } from "date-fns";
import es from 'date-fns/locale/es';

const iso8601Format = "yyyy-MM-dd'T'HH:mm:ss.SSSSSS'Z'";
const supportedDateFormats = [
    "EEEE d 'de' MMMM y",        // Ejemplo: "martes 19 de diciembre 2023"
    "E MMM d y",         // Ejemplo: "mar nov 14 2023"
    "dd/MM/yyyy",        // Ejemplo: "04/11/2023"
    "dd/MM/yy",          // Ejemplo: "04/11/23"
    "dd/MMM/yy",         // Ejemplo: "04/nov/23"
    "MM/dd/yyyy",        // Ejemplo: "11/04/2023"
    "yyyy/MM",           // Ejemplo: "2023/11"
    "yyyy/MM/dd",        // Ejemplo: "2023/11/04"
    "yyyy-MM-dd",        // Ejemplo: "2023-11-04"
    "MMMM d, yyyy",      // Ejemplo: "Noviembre 4, 2023"
    "d 'de' MMMM, yyyy", // Ejemplo: "4 de noviembre, 2023"
    "dd/MMM/yyyy",       // Ejemplo: "04/Nov/2023"
    "dd-MMM-yyyy",       // Ejemplo: "04-Nov-2023"
    "dd-MM-yyyy",        // Ejemplo: "04-11-2023"
    "dd.MM.yyyy",        // Ejemplo: "04.11.2023"
    "dd_MM_yyyy",        // Ejemplo: "04_11_2023"
    "EEEE",              // Ejemplo: "lunes, martes,..."
];
const supportedHoursFormats = [
    "HH",                // Ejemplo: "14"
    "mm",                // Ejemplo: "14"
    "ss",                // Ejemplo: "14"
    "HH:mm:ss",          // Ejemplo: "14:30:45"
    "HH:mm",             // Ejemplo: "14:30"
    "h:mm:ss a",         // Ejemplo: "2:30:45 PM"
    "hh:mm:ss a",        // Ejemplo: "02:30:45 PM"
    "h:mm a",            // Ejemplo: "2:30 PM"
    "hh:mm a",           // Ejemplo: "02:30 PM"
];

function valoresSoportados(){
  const supportedFormats = [];
  supportedFormats.push(iso8601Format);
  // Combinar formatos de fecha y hora
  for (const dateFormat of supportedDateFormats) {
    supportedFormats.push(dateFormat);
    for (const timeFormat of supportedHoursFormats) {
      if(!supportedFormats.includes(timeFormat))
        supportedFormats.push(timeFormat);
      supportedFormats.push(`${dateFormat} ${timeFormat}`);
    }
  }
  return supportedFormats;
}

const supportedFormats = valoresSoportados(); //todos los formatos soportados

export function detectarFormato(inputDate) {
  let parsedDate = null;
  for (const formatStr of supportedFormats) {
    try {
      parsedDate = parse(inputDate, formatStr, new Date(), { strict: false });
      if (parsedDate && !isNaN(parsedDate)) {
        return formatStr; // Retorna el formato que coincide
      }
    } catch (error) {}
  }
  return null; // Si no coincide con ningún formato
}

export function parsear(inputDate){
  let parsedDate = null;
  for (const formatStr of supportedFormats) {
    try {
      parsedDate = parse(inputDate, formatStr, new Date());
      if (isValid(parsedDate)) {
        return parsedDate;
      }
    } catch (error) {}
  }
  return null;
}

export function convertDate(inputDate, outputFormat) {
  // const iso8601Regex = /^(\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}\.\d{6}(Z|[-+]\d{2}:\d{2}))$/;
  // if (iso8601Regex.test(inputDate)) {
  if (!supportedFormats.includes(outputFormat)) {
    console.warn("Formato de fecha de salida no válido: 🚫"+outputFormat);
    console.warn(supportedFormats);
    return null;
    // throw new Error("Formato de fecha de salida no válido");
  }
  let parsedDate = null;
  if (inputDate instanceof Date) {
    parsedDate = inputDate;
  } else if (typeof inputDate === 'string'){
    parsedDate = parsear(inputDate);
    if (!parsedDate) {
      console.warn('Formato de fecha de entrada no válido: 🚫'+inputDate);// throw new Error("Formato de fecha de entrada no válido");
      return null;
    }
  }else{
    // console.warn('Formato de fecha de entrada no válido: 🚫'+inputDate);
    return null;
  }
  const result = format(parsedDate, outputFormat, { locale: es });
  // console.log('de '+inputDate+' a '+result);
  return result;
}

export function tiempoASeg(tiempo) {
  let partes = tiempo.split(':');
  let horas = parseInt(partes[0], 10) || 0;
  let minutos = parseInt(partes[1], 10) || 0;
  let segundos = parseInt(partes[2], 10) || 0;
  let totalSegundos = horas * 3600 + minutos * 60 + segundos;
  return totalSegundos;
}

export function tiempoANewDate(tiempo) {
  return new Date('1970-01-01T' + tiempo);
}

/**
 * Devuelve la cadena de fecha formateada en el formato dado. El resultado puede variar según la configuración regional.
 *
 * > ⚠️ Tenga en cuenta que los tokens `format` difieren de Moment.js y otras bibliotecas.
 * > Ver: https://github.com/date-fns/date-fns/blob/master/docs/unicodeTokens.md
 *
 * Los caracteres encerrados entre dos comillas simples (') se escapan.
 * Dos comillas simples seguidas, ya sea dentro o fuera de una secuencia de comillas, representan una comilla simple 'real'.
 * (véase el último ejemplo)
 *
 * El formato de la cadena se basa en la norma técnica Unicode nº 35:
 * https://www.unicode.org/reports/tr35/tr35-dates.html#Date_Field_Symbol_Table
 * con algunas adiciones (véase la nota 7 debajo de la tabla).
 *
Accepted patterns:
* | Unit                            | Pattern | Result examples                   | Notes |
* |---------------------------------|---------|-----------------------------------|-------|
* | Era                             | G..GGG  | AD, BC                            |       |
* |                                 | GGGG    | Anno Domini, Before Christ        | 2     |
* |                                 | GGGGG   | A, B                              |       |
* | Calendar year                   | y       | 44, 1, 1900, 2017                 | 5     |
* |                                 | yo      | 44th, 1st, 0th, 17th              | 5,7   |
* |                                 | yy      | 44, 01, 00, 17                    | 5     |
* |                                 | yyy     | 044, 001, 1900, 2017              | 5     |
* |                                 | yyyy    | 0044, 0001, 1900, 2017            | 5     |
* |                                 | yyyyy   | ...                               | 3,5   |
* | Local week-numbering year       | Y       | 44, 1, 1900, 2017                 | 5     |
* |                                 | Yo      | 44th, 1st, 1900th, 2017th         | 5,7   |
* |                                 | YY      | 44, 01, 00, 17                    | 5,8   |
* |                                 | YYY     | 044, 001, 1900, 2017              | 5     |
* |                                 | YYYY    | 0044, 0001, 1900, 2017            | 5,8   |
* |                                 | YYYYY   | ...                               | 3,5   |
* | ISO week-numbering year         | R       | -43, 0, 1, 1900, 2017             | 5,7   |
* |                                 | RR      | -43, 00, 01, 1900, 2017           | 5,7   |
* |                                 | RRR     | -043, 000, 001, 1900, 2017        | 5,7   |
* |                                 | RRRR    | -0043, 0000, 0001, 1900, 2017     | 5,7   |
* |                                 | RRRRR   | ...                               | 3,5,7 |
* | Extended year                   | u       | -43, 0, 1, 1900, 2017             | 5     |
* |                                 | uu      | -43, 01, 1900, 2017               | 5     |
* |                                 | uuu     | -043, 001, 1900, 2017             | 5     |
* |                                 | uuuu    | -0043, 0001, 1900, 2017           | 5     |
* |                                 | uuuuu   | ...                               | 3,5   |
* | Quarter (formatting)            | Q       | 1, 2, 3, 4                        |       |
* |                                 | Qo      | 1st, 2nd, 3rd, 4th                | 7     |
* |                                 | QQ      | 01, 02, 03, 04                    |       |
* |                                 | QQQ     | Q1, Q2, Q3, Q4                    |       |
* |                                 | QQQQ    | 1st quarter, 2nd quarter, ...     | 2     |
* |                                 | QQQQQ   | 1, 2, 3, 4                        | 4     |
* | Quarter (stand-alone)           | q       | 1, 2, 3, 4                        |       |
* |                                 | qo      | 1st, 2nd, 3rd, 4th                | 7     |
* |                                 | qq      | 01, 02, 03, 04                    |       |
* |                                 | qqq     | Q1, Q2, Q3, Q4                    |       |
* |                                 | qqqq    | 1st quarter, 2nd quarter, ...     | 2     |
* |                                 | qqqqq   | 1, 2, 3, 4                        | 4     |
* | Month (formatting)              | M       | 1, 2, ..., 12                     |       |
* |                                 | Mo      | 1st, 2nd, ..., 12th               | 7     |
* |                                 | MM      | 01, 02, ..., 12                   |       |
* |                                 | MMM     | Jan, Feb, ..., Dec                |       |
* |                                 | MMMM    | January, February, ..., December  | 2     |
* |                                 | MMMMM   | J, F, ..., D                      |       |
* | Month (stand-alone)             | L       | 1, 2, ..., 12                     |       |
* |                                 | Lo      | 1st, 2nd, ..., 12th               | 7     |
* |                                 | LL      | 01, 02, ..., 12                   |       |
* |                                 | LLL     | Jan, Feb, ..., Dec                |       |
* |                                 | LLLL    | January, February, ..., December  | 2     |
* |                                 | LLLLL   | J, F, ..., D                      |       |
* | Local week of year              | w       | 1, 2, ..., 53                     |       |
* |                                 | wo      | 1st, 2nd, ..., 53th               | 7     |
* |                                 | ww      | 01, 02, ..., 53                   |       |
* | ISO week of year                | I       | 1, 2, ..., 53                     | 7     |
* |                                 | Io      | 1st, 2nd, ..., 53th               | 7     |
* |                                 | II      | 01, 02, ..., 53                   | 7     |
* | Day of month                    | d       | 1, 2, ..., 31                     |       |
* |                                 | do      | 1st, 2nd, ..., 31st               | 7     |
* |                                 | dd      | 01, 02, ..., 31                   |       |
* | Day of year                     | D       | 1, 2, ..., 365, 366               | 9     |
* |                                 | Do      | 1st, 2nd, ..., 365th, 366th       | 7     |
* |                                 | DD      | 01, 02, ..., 365, 366             | 9     |
* |                                 | DDD     | 001, 002, ..., 365, 366           |       |
* |                                 | DDDD    | ...                               | 3     |
* | Day of week (formatting)        | E..EEE  | Mon, Tue, Wed, ..., Sun           |       |
* |                                 | EEEE    | Monday, Tuesday, ..., Sunday      | 2     |
* |                                 | EEEEE   | M, T, W, T, F, S, S               |       |
* |                                 | EEEEEE  | Mo, Tu, We, Th, Fr, Sa, Su        |       |
* | ISO day of week (formatting)    | i       | 1, 2, 3, ..., 7                   | 7     |
* |                                 | io      | 1st, 2nd, ..., 7th                | 7     |
* |                                 | ii      | 01, 02, ..., 07                   | 7     |
* |                                 | iii     | Mon, Tue, Wed, ..., Sun           | 7     |
* |                                 | iiii    | Monday, Tuesday, ..., Sunday      | 2,7   |
* |                                 | iiiii   | M, T, W, T, F, S, S               | 7     |
* |                                 | iiiiii  | Mo, Tu, We, Th, Fr, Sa, Su        | 7     |
* | Local day of week (formatting)  | e       | 2, 3, 4, ..., 1                   |       |
* |                                 | eo      | 2nd, 3rd, ..., 1st                | 7     |
* |                                 | ee      | 02, 03, ..., 01                   |       |
* |                                 | eee     | Mon, Tue, Wed, ..., Sun           |       |
* |                                 | eeee    | Monday, Tuesday, ..., Sunday      | 2     |
* |                                 | eeeee   | M, T, W, T, F, S, S               |       |
* |                                 | eeeeee  | Mo, Tu, We, Th, Fr, Sa, Su        |       |
* | Local day of week (stand-alone) | c       | 2, 3, 4, ..., 1                   |       |
* |                                 | co      | 2nd, 3rd, ..., 1st                | 7     |
* |                                 | cc      | 02, 03, ..., 01                   |       |
* |                                 | ccc     | Mon, Tue, Wed, ..., Sun           |       |
* |                                 | cccc    | Monday, Tuesday, ..., Sunday      | 2     |
* |                                 | ccccc   | M, T, W, T, F, S, S               |       |
* |                                 | cccccc  | Mo, Tu, We, Th, Fr, Sa, Su        |       |
* | AM, PM                          | a..aa   | AM, PM                            |       |
* |                                 | aaa     | am, pm                            |       |
* |                                 | aaaa    | a.m., p.m.                        | 2     |
* |                                 | aaaaa   | a, p                              |       |
* | AM, PM, noon, midnight          | b..bb   | AM, PM, noon, midnight            |       |
* |                                 | bbb     | am, pm, noon, midnight            |       |
* |                                 | bbbb    | a.m., p.m., noon, midnight        | 2     |
* |                                 | bbbbb   | a, p, n, mi                       |       |
* | Flexible day period             | B..BBB  | at night, in the morning, ...     |       |
* |                                 | BBBB    | at night, in the morning, ...     | 2     |
* |                                 | BBBBB   | at night, in the morning, ...     |       |
* | Hour [1-12]                     | h       | 1, 2, ..., 11, 12                 |       |
* |                                 | ho      | 1st, 2nd, ..., 11th, 12th         | 7     |
* |                                 | hh      | 01, 02, ..., 11, 12               |       |
* | Hour [0-23]                     | H       | 0, 1, 2, ..., 23                  |       |
* |                                 | Ho      | 0th, 1st, 2nd, ..., 23rd          | 7     |
* |                                 | HH      | 00, 01, 02, ..., 23               |       |
* | Hour [0-11]                     | K       | 1, 2, ..., 11, 0                  |       |
* |                                 | Ko      | 1st, 2nd, ..., 11th, 0th          | 7     |
* |                                 | KK      | 01, 02, ..., 11, 00               |       |
* | Hour [1-24]                     | k       | 24, 1, 2, ..., 23                 |       |
* |                                 | ko      | 24th, 1st, 2nd, ..., 23rd         | 7     |
* |                                 | kk      | 24, 01, 02, ..., 23               |       |
* | Minute                          | m       | 0, 1, ..., 59                     |       |
* |                                 | mo      | 0th, 1st, ..., 59th               | 7     |
* |                                 | mm      | 00, 01, ..., 59                   |       |
* | Second                          | s       | 0, 1, ..., 59                     |       |
* |                                 | so      | 0th, 1st, ..., 59th               | 7     |
* |                                 | ss      | 00, 01, ..., 59                   |       |
* | Fraction of second              | S       | 0, 1, ..., 9                      |       |
* |                                 | SS      | 00, 01, ..., 99                   |       |
* |                                 | SSS     | 000, 001, ..., 999                |       |
* |                                 | SSSS    | ...                               | 3     |
* | Timezone (ISO-8601 w/ Z)        | X       | -08, +0530, Z                     |       |
* |                                 | XX      | -0800, +0530, Z                   |       |
* |                                 | XXX     | -08:00, +05:30, Z                 |       |
* |                                 | XXXX    | -0800, +0530, Z, +123456          | 2     |
* |                                 | XXXXX   | -08:00, +05:30, Z, +12:34:56      |       |
* | Timezone (ISO-8601 w/o Z)       | x       | -08, +0530, +00                   |       |
* |                                 | xx      | -0800, +0530, +0000               |       |
* |                                 | xxx     | -08:00, +05:30, +00:00            | 2     |
* |                                 | xxxx    | -0800, +0530, +0000, +123456      |       |
* |                                 | xxxxx   | -08:00, +05:30, +00:00, +12:34:56 |       |
* | Timezone (GMT)                  | O...OOO | GMT-8, GMT+5:30, GMT+0            |       |
* |                                 | OOOO    | GMT-08:00, GMT+05:30, GMT+00:00   | 2     |
* | Timezone (specific non-locat.)  | z...zzz | GMT-8, GMT+5:30, GMT+0            | 6     |
* |                                 | zzzz    | GMT-08:00, GMT+05:30, GMT+00:00   | 2,6   |
* | Seconds timestamp               | t       | 512969520                         | 7     |
* |                                 | tt      | ...                               | 3,7   |
* | Milliseconds timestamp          | T       | 512969520900                      | 7     |
* |                                 | TT      | ...                               | 3,7   |
* | Long localized date             | P       | 04/29/1453                        | 7     |
* |                                 | PP      | Apr 29, 1453                      | 7     |
* |                                 | PPP     | April 29th, 1453                  | 7     |
* |                                 | PPPP    | Friday, April 29th, 1453          | 2,7   |
* | Long localized time             | p       | 12:00 AM                          | 7     |
* |                                 | pp      | 12:00:00 AM                       | 7     |
* |                                 | ppp     | 12:00:00 AM GMT+2                 | 7     |
* |                                 | pppp    | 12:00:00 AM GMT+02:00             | 2,7   |
* | Combination of date and time    | Pp      | 04/29/1453, 12:00 AM              | 7     |
* |                                 | PPpp    | Apr 29, 1453, 12:00:00 AM         | 7     |
* |                                 | PPPppp  | April 29th, 1453 at ...           | 7     |
* |                                 | PPPPpppp| Friday, April 29th, 1453 at ...   | 2,7   |
Notes:
 * 1. Las unidades de "formato" (por ejemplo, el formato del trimestre) en la configuración regional por defecto en-US
 * son iguales que las unidades "independientes", pero son diferentes en algunos idiomas.
 * Las unidades de "formato" se declinan según las reglas del idioma
 * en el contexto de una fecha. Las unidades "independientes" son siempre nominativas singulares:
 *
 *    `format(new Date(2017, 10, 6), 'do LLLL', {locale: cs}) //=> '6. listopad'`
 *
 *    `format(new Date(2017, 10, 6), 'do MMMM', {locale: cs}) //=> '6. listopadu'`
 *
 * 2. Cualquier secuencia de letras idénticas es un patrón, a menos que se escape mediante
 * las comillas simples (véase más abajo).
 * Si la secuencia es más larga que la que aparece en la tabla (por ejemplo, `EEEEEEEEEEE`)
 * la salida será la misma que el patrón por defecto para esta unidad, normalmente
 * el más largo (en el caso de los días laborables ISO, `EEEE`). Los patrones por defecto para las unidades
 * están marcados con "2" en la última columna de la tabla.
 *
 *    `format(new Date(2017, 10, 6), 'MMM') //=> 'Nov'`
 *
 *    `format(new Date(2017, 10, 6), 'MMMM') //=> 'November'`
 *
 *    `format(new Date(2017, 10, 6), 'MMMMM') //=> 'N'`
 *
 *    `format(new Date(2017, 10, 6), 'MMMMMM') //=> 'November'`
 *
 *    `format(new Date(2017, 10, 6), 'MMMMMMM') //=> 'November'`
 *
 * 3. Algunos patrones pueden tener una longitud ilimitada (como `yyyyyyyy`).
 * La salida se rellenará con ceros para que coincida con la longitud del patrón.
 *
 *    `format(new Date(2017, 10, 6), 'yyyyyyyy') //=> '00002017'`
 *
 * 4. `QQQQQ` y `qqqqq` podrían no ser estrictamente numéricos en algunos locales.
 * Estos tokens representan la forma más corta del trimestre.
 *
 * 5. La principal diferencia entre los patrones `y` y `u` son los años B.C:
 *
 *    | Year | `y` | `u` |
 *    |------|-----|-----|
 *    | AC 1 |   1 |   1 |
 *    | BC 1 |   1 |   0 |
 *    | BC 2 |   2 |  -1 |
 *
 *    Además, `yy` siempre devuelve los dos últimos dígitos de un año,
 *    mientras que `uu` reduce los años de un solo dígito a 2 caracteres y devuelve los demás años sin cambios:
 *
 *    | Year | `yy` | `uu` |
 *    |------|------|------|
 *    | 1    |   01 |   01 |
 *    | 14   |   14 |   14 |
 *    | 376  |   76 |  376 |
 *    | 1453 |   53 | 1453 |
 *
 *    La misma diferencia se aplica a los años de numeración semanal local e ISO (`Y` y `R`),
 *    excepto que los años de numeración de semana locales dependen de `options.weekStartsOn` y `options.firstWeekContainsDate` (compare [getISOWeekYear] con [getISOWeekYear])
 *    y `options.firstWeekContainsDate` (compare [getISOWeekYear]{@link https://date-fns.org/docs/getISOWeekYear}
 *    y [getWeekYear]{@link https://date-fns.org/docs/getWeekYear}).
 *
 * 6. Las zonas horarias específicas no localizadas no están disponibles actualmente en `date-fns`,
 * así que ahora mismo estos tokens vuelven a las zonas horarias GMT.
 *
 * 7. Estos patrones no están en el Unicode Technical Standard #35:
 *  - `i`: ISO día de la semana
 *  - `I`: ISO semana del año
 *  - `R`: ISO semana del año
 *  - `t`: segundos timestamp
 *  - `T`: marca de tiempo en milisegundos
 *  - `o`: modificador de número ordinal
 *  - `P`: fecha localizada larga
 *  - `p`: hora localizada larga
 *
 * 8. Los tokens `YY` y `YYYY` representan años con numeración de semana pero a menudo se confunden con años.
 * Debe activar `options.useAdditionalWeekYearTokens` para utilizarlos. Véase: https://github.com/date-fns/date-fns/blob/master/docs/unicodeTokens.md
 *
 * 9. Los tokens `D` y `DD` representan días del año pero a menudo se confunden con días del mes.
 * Debe activar `options.useAdditionalDayOfYearTokens` para utilizarlos. Véase: https://github.com/date-fns/date-fns/blob/master/docs/unicodeTokens.md
*/

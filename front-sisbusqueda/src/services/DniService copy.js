import { dni, api } from "src/boot/axios";

class DniService {
  /**
   * 🔹 Consulta directa a la API de ApisPeru (v2/reniec/dni)
   */
  static async dni(numero) {
    try {
      const response = await dni.get("v2/reniec/dni", {
        params: {
          numero, // Número de DNI
        },
      });
      return response.data; // Retorna los datos obtenidos
    } catch (error) {
      console.error("❌ Error al consultar el DNI:", error);
      throw new Error("No se pudo obtener la información del DNI.");
    }
  }

  /**
   * 🔹 Consulta al backend Laravel (controlador SolicitanteController)
   */
  static async getSolicitanteDni(_dni) {
    try {
      const response = await api.get(`/api/solicitantes/dni/${_dni}`);
      return response.data; // Retorna los datos obtenidos
    } catch (error) {
      console.error("❌ Error al consultar el solicitante por DNI:", error);
      throw new Error("No se pudo obtener la información del solicitante.");
    }
  }
}

export default DniService;

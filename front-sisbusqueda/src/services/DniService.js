import { dni, api } from "src/boot/axios";
import { secureStorage } from "src/utils/SecureStorage";

class DniService {
  /**
   * Consulta DNI directamente a la API de ApisPeru
   */
  static async consultarDni(numero) {
    try {
      const response = await dni.get("v2/reniec/dni", {
        params: {
          numero: numero,
        },
      });

      // Almacenar en SecureStorage
      if (response.data) {
        secureStorage.setItem(`dni_${numero}`, response.data);
      }

      return response.data;
    } catch (error) {
      console.error("Error consultando DNI:", error);
      throw error;
    }
  }

  /**
   * Consulta DNI a través del backend Laravel
   */
  static async getSolicitanteDni(numero) {
    try {
      // Primero intentar obtener del SecureStorage
      const cached = secureStorage.getItem(`dni_${numero}`);
      if (cached) return cached;

      const response = await api.get(`/api/solicitante/dni/${numero}`);
      return response.data;
    } catch (error) {
      console.error("Error obteniendo solicitante por DNI:", error);
      throw error;
    }
  }
}

export default DniService;

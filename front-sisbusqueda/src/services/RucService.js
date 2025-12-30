import { dni, api } from "src/boot/axios";
import { secureStorage } from "src/utils/SecureStorage";

class RucService {
  /**
   * Consulta RUC directamente a la API de ApisPeru
   */
  static async consultarRuc(numero) {
    try {
      const response = await dni.get("v1/ruc", {
        params: {
          numero: numero
        },
      });

      // Almacenar en SecureStorage
      if (response.data) {
        secureStorage.setItem(`ruc_${numero}`, response.data);
      }

      return response.data;
    } catch (error) {
      console.error("Error consultando RUC:", error);
      throw error;
    }
  }

  /**
   * Consulta RUC a través del backend Laravel
   */
  static async getSolicitanteRuc(numero) {
    try {
      const cached = secureStorage.getItem(`ruc_${numero}`);
      if (cached) return cached;

      const response = await api.get(`/api/solicitante/ruc/${numero}`);
      return response.data;
    } catch (error) {
      console.error("Error obteniendo solicitante por RUC:", error);
      throw error;
    }
  }
}



export default RucService;

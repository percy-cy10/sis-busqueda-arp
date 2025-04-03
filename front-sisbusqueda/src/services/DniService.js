import { dni, api } from "src/boot/axios";

class DniService {
  static async dni(dni) {
    return await dni.get("v2/reniec/dni", {
      params: {
        numero: dni,
      },
    });
  }
  static async getSolicitanteDni(_dni) {
    return (await api.get(`/api/solicitantes/dni/${_dni}`)).data;
  }
}

export default DniService;
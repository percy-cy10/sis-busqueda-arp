import { api } from "src/boot/axios";

class ArbolitoService {
  static async getData(params) {
    return (await api.get("api/arbolitos", params)).data;
  }

  static async getPermissions() {
    return (await api.get("api/permissions")).data;
  }

  static async get(id) {
    return (await api.get(`api/arbolitos/${id}`)).data;
  }

  static async save(reg) {
    if (reg.id === undefined || reg.id === null) {
      return (await api.post("api/arbolitos", reg)).data;
    } else {
      return (await api.put(`api/arbolitos/${reg.id}`, reg)).data;
    }
  }

  static async delete(id) {
    return await api.delete(`api/arbolitos/${id}`);
  }
}

export default ArbolitoService;

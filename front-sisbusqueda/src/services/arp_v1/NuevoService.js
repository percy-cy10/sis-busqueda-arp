import { api } from "src/boot/axios";

class NuevoService {
  static async getData(params) {
    return (await api.get("api/nuevos", params)).data;
  }

  static async getPermissions() {
    return (await api.get("api/permissions")).data;
  }

  static async get(id) {
    return (await api.get(`api/nuevos/${id}`)).data;
  }

  static async save(reg) {
    if (reg.id === undefined || reg.id === null) {
      return (await api.post("api/nuevos", reg)).data;
    } else {
      return (await api.put(`api/nuevos/${reg.id}`, reg)).data;
    }
  }

  static async delete(id) {
    return await api.delete(`api/nuevos/${id}`);
  }
}

export default NuevoService;

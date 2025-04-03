import { api } from "src/boot/axios";

class AnteriorService {
  static async getData(params) {
    return (await api.get("api/anteriores", params)).data;
  }

  static async getPermissions() {
    return (await api.get("api/permissions")).data;
  }

  static async get(id) {
    return (await api.get(`api/anteriores/${id}`)).data;
  }

  static async save(reg) {
    if (reg.id === undefined || reg.id === null) {
      return (await api.post("api/anteriores", reg)).data;
    } else {
      return (await api.put(`api/anteriores/${reg.id}`, reg)).data;
    }
  }

  static async delete(id) {
    return await api.delete(`api/anteriores/${id}`);
  }
}

export default AnteriorService;

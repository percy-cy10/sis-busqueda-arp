import { api } from "src/boot/axios";

class SiaService {
  static async getData(params) {
    return (await api.get("api/sia", params)).data;
  }

  static async getPermissions() {
    return (await api.get("api/permissions")).data;
  }

  static async get(id) {
    return (await api.get(`api/sia/${id}`)).data;
  }

  static async save(reg) {
    if (reg.id === undefined || reg.id === null) {
      return (await api.post("api/sia", reg)).data;
    } else {
      return (await api.put(`api/sia/${reg.id}`, reg)).data;
    }
  }

  static async delete(id) {
    return await api.delete(`api/sia/${id}`);
  }
}

export default SiaService;

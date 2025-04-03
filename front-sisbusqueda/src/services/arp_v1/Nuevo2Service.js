import { api } from "src/boot/axios";

class Nuevo2Service {
  static async getData(params) {
    return (await api.get("api/nuevos2", params)).data;
  }

  static async getPermissions() {
    return (await api.get("api/permissions")).data;
  }

  static async get(id) {
    return (await api.get(`api/nuevos2/${id}`)).data;
  }

  static async save(reg) {
    if (reg.id === undefined || reg.id === null) {
      return (await api.post("api/nuevos2", reg)).data;
    } else {
      return (await api.put(`api/nuevos2/${reg.id}`, reg)).data;
    }
  }

  static async delete(id) {
    return await api.delete(`api/nuevos2/${id}`);
  }
}

export default Nuevo2Service;

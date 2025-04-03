import { api } from "src/boot/axios";

class Anterior2Service {
  static async getData(params) {
    return (await api.get("api/anteriores2", params)).data;
  }

  static async getPermissions() {
    return (await api.get("api/permissions")).data;
  }

  static async get(id) {
    return (await api.get(`api/anteriores2/${id}`)).data;
  }

  static async save(reg) {
    if (reg.id === undefined || reg.id === null) {
      return (await api.post("api/anteriores2", reg)).data;
    } else {
      return (await api.put(`api/anteriores2/${reg.id}`, reg)).data;
    }
  }

  static async delete(id) {
    return await api.delete(`api/anteriores2/${id}`);
  }
}

export default Anterior2Service;

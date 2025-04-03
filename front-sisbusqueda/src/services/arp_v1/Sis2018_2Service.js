import { api } from "src/boot/axios";

class Sis2018_2Service {
  static async getData(params) {
    return (await api.get("api/sis2018_2", params)).data;
  }

  static async getPermissions() {
    return (await api.get("api/permissions")).data;
  }

  static async get(id) {
    return (await api.get(`api/sis2018_2/${id}`)).data;
  }

  static async save(reg) {
    if (reg.id === undefined || reg.id === null) {
      return (await api.post("api/sis2018_2", reg)).data;
    } else {
      return (await api.put(`api/sis2018_2/${reg.id}`, reg)).data;
    }
  }

  static async delete(id) {
    return await api.delete(`api/sis2018_2/${id}`);
  }
}

export default Sis2018_2Service;

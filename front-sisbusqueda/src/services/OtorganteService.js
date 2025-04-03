import { api } from "src/boot/axios";

class OtorganteService {
  static async getData(params) {
    return (await api.get("/api/otorgantes", params)).data;
  }

  static async get(id) {
    return (await api.get(`/api/otorgantes/${id}`)).data;
  }

  static async delete(id) {
    return await api.delete(`/api/otorgantes/${id}`);
  }

  static async save(reg) {
    if (reg.id === undefined || reg.id === null) {
      return (await api.post("/api/otorgantes", reg)).data;
    } else {
      return (await api.put(`/api/otorgantes/${reg.id}`, reg)).data;
    }
  }
}

export default OtorganteService;

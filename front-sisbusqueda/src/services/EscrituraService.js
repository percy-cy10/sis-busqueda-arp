import { api } from "src/boot/axios";

class EscrituraService {
  static async getData(params) {
    return (await api.get("/api/escrituras", params)).data;
  }

  static async get(id) {
    return (await api.get(`/api/escrituras/${id}`)).data;
  }

  static async delete(id) {
    return await api.delete(`/api/escrituras/${id}`);
  }

  static async save(reg) {
    if (reg.id === undefined || reg.id === null) {
      return (await api.post("/api/escrituras", reg)).data;
    } else {
      return (await api.put(`/api/escrituras/${reg.id}`, reg)).data;
    }
  }
}

export default EscrituraService;

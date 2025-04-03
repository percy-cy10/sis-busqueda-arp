import { defineStore } from "pinia";
import { api } from "src/boot/axios";
import { Cookies } from "quasar";
import { secureStorage } from "src/utils/SecureStorage";

export const useUserStore = defineStore("user", {
  state: () => ({
    id: null,
    name: null,
    email: null,
    area_id: null,
    roles: null,
    permisos: null,
    area: null,
  }),
  getters: {
    getId: (state) => state.id,
    getName: (state) => state.name,
    getEmail: (state) => state.email,
    getRoles: (state) => state.roles,
    getPermisos: (state) => state.permisos,
    getArea: (state) => state.area,
    getAreaId: (state) => state.area_id
  },
  actions: {
    async login(email, password) {
      Cookies.remove("token");
      secureStorage.removeItem("user");
      try {
        const res = await api.post("oauth/token", {
          grant_type: "password",
          client_id: import.meta.env.VITE_APP_ID,
          client_secret: import.meta.env.VITE_APP_SECRET,
          username: email,
          password: password,
          scope: "",
        });
        // .then(() => {
        let tokenString = "Bearer " + res.data.access_token;
        Cookies.set("token", tokenString, { path: "/" });
        // });

        // return res;
      } catch (e) {
        if (e) throw e;
      }
      // this.getUser();
    },
    async getUser() {
      try {
        const user = await api.get("api/user");
        secureStorage.setItem("user", user.data.user);
        // console.log(user.data);
        this.setUser(user.data);
        console.log(user.data.user);
      } catch (e) {
        if (e) throw e;
      }
    },

    logout() {
      Cookies.remove("token");
      // secureStorage.clear();
      this.clearUser();
    },

    setUser(payload) {
      if (payload.user.id) this.id = payload.user.id;
      if (payload.user.name) this.name = payload.user.name;
      if (payload.user.email) this.email = payload.user.email;
      if (payload.user.area_id) this.area_id = payload.user.area_id;
      if (payload.permisos) this.permisos = payload.permisos;
      if (payload.roles) this.roles = payload.roles;
      if (payload.user.area?.nombre) this.area = payload.user.area.nombre;
    },

    clearUser() {
      this.id = null;
      this.name = null;
      this.email = null;
      this.area_id = null;
      this.permisos = null;
      this.roles = null;
      this.area = null;
    },
    hasPermission(permission) {
      // const areaId = userStore.areaId; // Assuming `areaId` is stored in `userStore`
      //   if (areaId === null || areaId === "N/A") {
      //     return true; // Grant all permissions
      //   }
      return this.permisos?.includes(permission);
    },
  },
});

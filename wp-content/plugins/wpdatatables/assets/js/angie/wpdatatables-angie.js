var Ea;
const nf = /* @__PURE__ */ Object.freeze({
  status: "aborted"
});
function M(t, e, r) {
  function n(a, c) {
    if (a._zod || Object.defineProperty(a, "_zod", {
      value: {
        def: c,
        constr: o,
        traits: /* @__PURE__ */ new Set()
      },
      enumerable: !1
    }), a._zod.traits.has(t))
      return;
    a._zod.traits.add(t), e(a, c);
    const u = o.prototype, l = Object.keys(u);
    for (let h = 0; h < l.length; h++) {
      const p = l[h];
      p in a || (a[p] = u[p].bind(a));
    }
  }
  const s = r?.Parent ?? Object;
  class i extends s {
  }
  Object.defineProperty(i, "name", { value: t });
  function o(a) {
    var c;
    const u = r?.Parent ? new i() : this;
    n(u, a), (c = u._zod).deferred ?? (c.deferred = []);
    for (const l of u._zod.deferred)
      l();
    return u;
  }
  return Object.defineProperty(o, "init", { value: n }), Object.defineProperty(o, Symbol.hasInstance, {
    value: (a) => r?.Parent && a instanceof r.Parent ? !0 : a?._zod?.traits?.has(t)
  }), Object.defineProperty(o, "name", { value: t }), o;
}
class br extends Error {
  constructor() {
    super("Encountered Promise during synchronous parse. Use .parseAsync() instead.");
  }
}
class _l extends Error {
  constructor(e) {
    super(`Encountered unidirectional transform during encode: ${e}`), this.name = "ZodEncodeError";
  }
}
(Ea = globalThis).__zod_globalConfig ?? (Ea.__zod_globalConfig = {});
const Oo = globalThis.__zod_globalConfig;
function Pt(t) {
  return Oo;
}
function yl(t) {
  const e = Object.values(t).filter((n) => typeof n == "number");
  return Object.entries(t).filter(([n, s]) => e.indexOf(+n) === -1).map(([n, s]) => s);
}
function Hi(t, e) {
  return typeof e == "bigint" ? e.toString() : e;
}
function Fs(t) {
  return {
    get value() {
      {
        const e = t();
        return Object.defineProperty(this, "value", { value: e }), e;
      }
    }
  };
}
function Ao(t) {
  return t == null;
}
function No(t) {
  const e = t.startsWith("^") ? 1 : 0, r = t.endsWith("$") ? t.length - 1 : t.length;
  return t.slice(e, r);
}
function sf(t, e) {
  const r = t / e, n = Math.round(r), s = Number.EPSILON * Math.max(Math.abs(r), 1);
  return Math.abs(r - n) < s ? 0 : r - n;
}
const Ta = /* @__PURE__ */ Symbol("evaluating");
function Se(t, e, r) {
  let n;
  Object.defineProperty(t, e, {
    get() {
      if (n !== Ta)
        return n === void 0 && (n = Ta, n = r()), n;
    },
    set(s) {
      Object.defineProperty(t, e, {
        value: s
        // configurable: true,
      });
    },
    configurable: !0
  });
}
function ir(t, e, r) {
  Object.defineProperty(t, e, {
    value: r,
    writable: !0,
    enumerable: !0,
    configurable: !0
  });
}
function Ft(...t) {
  const e = {};
  for (const r of t) {
    const n = Object.getOwnPropertyDescriptors(r);
    Object.assign(e, n);
  }
  return Object.defineProperties({}, e);
}
function Ra(t) {
  return JSON.stringify(t);
}
function of(t) {
  return t.toLowerCase().trim().replace(/[^\w\s-]/g, "").replace(/[\s_-]+/g, "-").replace(/^-+|-+$/g, "");
}
const wl = "captureStackTrace" in Error ? Error.captureStackTrace : (...t) => {
};
function Xr(t) {
  return typeof t == "object" && t !== null && !Array.isArray(t);
}
const af = /* @__PURE__ */ Fs(() => {
  if (Oo.jitless || typeof navigator < "u" && navigator?.userAgent?.includes("Cloudflare"))
    return !1;
  try {
    const t = Function;
    return new t(""), !0;
  } catch {
    return !1;
  }
});
function Sr(t) {
  if (Xr(t) === !1)
    return !1;
  const e = t.constructor;
  if (e === void 0 || typeof e != "function")
    return !0;
  const r = e.prototype;
  return !(Xr(r) === !1 || Object.prototype.hasOwnProperty.call(r, "isPrototypeOf") === !1);
}
function vl(t) {
  return Sr(t) ? { ...t } : Array.isArray(t) ? [...t] : t instanceof Map ? new Map(t) : t instanceof Set ? new Set(t) : t;
}
const cf = /* @__PURE__ */ new Set(["string", "number", "symbol"]);
function kr(t) {
  return t.replace(/[.*+?^${}()|[\]\\]/g, "\\$&");
}
function xt(t, e, r) {
  const n = new t._zod.constr(e ?? t._zod.def);
  return (!e || r?.parent) && (n._zod.parent = t), n;
}
function Q(t) {
  const e = t;
  if (!e)
    return {};
  if (typeof e == "string")
    return { error: () => e };
  if (e?.message !== void 0) {
    if (e?.error !== void 0)
      throw new Error("Cannot specify both `message` and `error` params");
    e.error = e.message;
  }
  return delete e.message, typeof e.error == "string" ? { ...e, error: () => e.error } : e;
}
function uf(t) {
  return Object.keys(t).filter((e) => t[e]._zod.optin === "optional" && t[e]._zod.optout === "optional");
}
const lf = {
  safeint: [Number.MIN_SAFE_INTEGER, Number.MAX_SAFE_INTEGER],
  int32: [-2147483648, 2147483647],
  uint32: [0, 4294967295],
  float32: [-34028234663852886e22, 34028234663852886e22],
  float64: [-Number.MAX_VALUE, Number.MAX_VALUE]
};
function df(t, e) {
  const r = t._zod.def, n = r.checks;
  if (n && n.length > 0)
    throw new Error(".pick() cannot be used on object schemas containing refinements");
  const i = Ft(t._zod.def, {
    get shape() {
      const o = {};
      for (const a in e) {
        if (!(a in r.shape))
          throw new Error(`Unrecognized key: "${a}"`);
        e[a] && (o[a] = r.shape[a]);
      }
      return ir(this, "shape", o), o;
    },
    checks: []
  });
  return xt(t, i);
}
function hf(t, e) {
  const r = t._zod.def, n = r.checks;
  if (n && n.length > 0)
    throw new Error(".omit() cannot be used on object schemas containing refinements");
  const i = Ft(t._zod.def, {
    get shape() {
      const o = { ...t._zod.def.shape };
      for (const a in e) {
        if (!(a in r.shape))
          throw new Error(`Unrecognized key: "${a}"`);
        e[a] && delete o[a];
      }
      return ir(this, "shape", o), o;
    },
    checks: []
  });
  return xt(t, i);
}
function ff(t, e) {
  if (!Sr(e))
    throw new Error("Invalid input to extend: expected a plain object");
  const r = t._zod.def.checks;
  if (r && r.length > 0) {
    const i = t._zod.def.shape;
    for (const o in e)
      if (Object.getOwnPropertyDescriptor(i, o) !== void 0)
        throw new Error("Cannot overwrite keys on object schemas containing refinements. Use `.safeExtend()` instead.");
  }
  const s = Ft(t._zod.def, {
    get shape() {
      const i = { ...t._zod.def.shape, ...e };
      return ir(this, "shape", i), i;
    }
  });
  return xt(t, s);
}
function pf(t, e) {
  if (!Sr(e))
    throw new Error("Invalid input to safeExtend: expected a plain object");
  const r = Ft(t._zod.def, {
    get shape() {
      const n = { ...t._zod.def.shape, ...e };
      return ir(this, "shape", n), n;
    }
  });
  return xt(t, r);
}
function mf(t, e) {
  if (t._zod.def.checks?.length)
    throw new Error(".merge() cannot be used on object schemas containing refinements. Use .safeExtend() instead.");
  const r = Ft(t._zod.def, {
    get shape() {
      const n = { ...t._zod.def.shape, ...e._zod.def.shape };
      return ir(this, "shape", n), n;
    },
    get catchall() {
      return e._zod.def.catchall;
    },
    checks: e._zod.def.checks ?? []
  });
  return xt(t, r);
}
function gf(t, e, r) {
  const s = e._zod.def.checks;
  if (s && s.length > 0)
    throw new Error(".partial() cannot be used on object schemas containing refinements");
  const o = Ft(e._zod.def, {
    get shape() {
      const a = e._zod.def.shape, c = { ...a };
      if (r)
        for (const u in r) {
          if (!(u in a))
            throw new Error(`Unrecognized key: "${u}"`);
          r[u] && (c[u] = t ? new t({
            type: "optional",
            innerType: a[u]
          }) : a[u]);
        }
      else
        for (const u in a)
          c[u] = t ? new t({
            type: "optional",
            innerType: a[u]
          }) : a[u];
      return ir(this, "shape", c), c;
    },
    checks: []
  });
  return xt(e, o);
}
function _f(t, e, r) {
  const n = Ft(e._zod.def, {
    get shape() {
      const s = e._zod.def.shape, i = { ...s };
      if (r)
        for (const o in r) {
          if (!(o in i))
            throw new Error(`Unrecognized key: "${o}"`);
          r[o] && (i[o] = new t({
            type: "nonoptional",
            innerType: s[o]
          }));
        }
      else
        for (const o in s)
          i[o] = new t({
            type: "nonoptional",
            innerType: s[o]
          });
      return ir(this, "shape", i), i;
    }
  });
  return xt(e, n);
}
function pr(t, e = 0) {
  if (t.aborted === !0)
    return !0;
  for (let r = e; r < t.issues.length; r++)
    if (t.issues[r]?.continue !== !0)
      return !0;
  return !1;
}
function yf(t, e = 0) {
  if (t.aborted === !0)
    return !0;
  for (let r = e; r < t.issues.length; r++)
    if (t.issues[r]?.continue === !1)
      return !0;
  return !1;
}
function mr(t, e) {
  return e.map((r) => {
    var n;
    return (n = r).path ?? (n.path = []), r.path.unshift(t), r;
  });
}
function $n(t) {
  return typeof t == "string" ? t : t?.message;
}
function Ct(t, e, r) {
  const n = t.message ? t.message : $n(t.inst?._zod.def?.error?.(t)) ?? $n(e?.error?.(t)) ?? $n(r.customError?.(t)) ?? $n(r.localeError?.(t)) ?? "Invalid input", { inst: s, continue: i, input: o, ...a } = t;
  return a.path ?? (a.path = []), a.message = n, e?.reportInput && (a.input = o), a;
}
function xo(t) {
  return Array.isArray(t) ? "array" : typeof t == "string" ? "string" : "unknown";
}
function en(...t) {
  const [e, r, n] = t;
  return typeof e == "string" ? {
    message: e,
    code: "custom",
    input: r,
    inst: n
  } : { ...e };
}
const bl = (t, e) => {
  t.name = "$ZodError", Object.defineProperty(t, "_zod", {
    value: t._zod,
    enumerable: !1
  }), Object.defineProperty(t, "issues", {
    value: e,
    enumerable: !1
  }), t.message = JSON.stringify(e, Hi, 2), Object.defineProperty(t, "toString", {
    value: () => t.message,
    enumerable: !1
  });
}, Sl = M("$ZodError", bl), Vs = M("$ZodError", bl, { Parent: Error });
function wf(t, e = (r) => r.message) {
  const r = {}, n = [];
  for (const s of t.issues)
    s.path.length > 0 ? (r[s.path[0]] = r[s.path[0]] || [], r[s.path[0]].push(e(s))) : n.push(e(s));
  return { formErrors: n, fieldErrors: r };
}
function vf(t, e = (r) => r.message) {
  const r = { _errors: [] }, n = (s, i = []) => {
    for (const o of s.issues)
      if (o.code === "invalid_union" && o.errors.length)
        o.errors.map((a) => n({ issues: a }, [...i, ...o.path]));
      else if (o.code === "invalid_key")
        n({ issues: o.issues }, [...i, ...o.path]);
      else if (o.code === "invalid_element")
        n({ issues: o.issues }, [...i, ...o.path]);
      else {
        const a = [...i, ...o.path];
        if (a.length === 0)
          r._errors.push(e(o));
        else {
          let c = r, u = 0;
          for (; u < a.length; ) {
            const l = a[u];
            u === a.length - 1 ? (c[l] = c[l] || { _errors: [] }, c[l]._errors.push(e(o))) : c[l] = c[l] || { _errors: [] }, c = c[l], u++;
          }
        }
      }
  };
  return n(t), r;
}
const Bs = (t) => (e, r, n, s) => {
  const i = n ? { ...n, async: !1 } : { async: !1 }, o = e._zod.run({ value: r, issues: [] }, i);
  if (o instanceof Promise)
    throw new br();
  if (o.issues.length) {
    const a = new (s?.Err ?? t)(o.issues.map((c) => Ct(c, i, Pt())));
    throw wl(a, s?.callee), a;
  }
  return o.value;
}, bf = /* @__PURE__ */ Bs(Vs), Ws = (t) => async (e, r, n, s) => {
  const i = n ? { ...n, async: !0 } : { async: !0 };
  let o = e._zod.run({ value: r, issues: [] }, i);
  if (o instanceof Promise && (o = await o), o.issues.length) {
    const a = new (s?.Err ?? t)(o.issues.map((c) => Ct(c, i, Pt())));
    throw wl(a, s?.callee), a;
  }
  return o.value;
}, Sf = /* @__PURE__ */ Ws(Vs), Gs = (t) => (e, r, n) => {
  const s = n ? { ...n, async: !1 } : { async: !1 }, i = e._zod.run({ value: r, issues: [] }, s);
  if (i instanceof Promise)
    throw new br();
  return i.issues.length ? {
    success: !1,
    error: new (t ?? Sl)(i.issues.map((o) => Ct(o, s, Pt())))
  } : { success: !0, data: i.value };
}, zo = /* @__PURE__ */ Gs(Vs), Js = (t) => async (e, r, n) => {
  const s = n ? { ...n, async: !0 } : { async: !0 };
  let i = e._zod.run({ value: r, issues: [] }, s);
  return i instanceof Promise && (i = await i), i.issues.length ? {
    success: !1,
    error: new t(i.issues.map((o) => Ct(o, s, Pt())))
  } : { success: !0, data: i.value };
}, jo = /* @__PURE__ */ Js(Vs), kf = (t) => (e, r, n) => {
  const s = n ? { ...n, direction: "backward" } : { direction: "backward" };
  return Bs(t)(e, r, s);
}, $f = (t) => (e, r, n) => Bs(t)(e, r, n), Ef = (t) => async (e, r, n) => {
  const s = n ? { ...n, direction: "backward" } : { direction: "backward" };
  return Ws(t)(e, r, s);
}, Tf = (t) => async (e, r, n) => Ws(t)(e, r, n), Rf = (t) => (e, r, n) => {
  const s = n ? { ...n, direction: "backward" } : { direction: "backward" };
  return Gs(t)(e, r, s);
}, If = (t) => (e, r, n) => Gs(t)(e, r, n), Pf = (t) => async (e, r, n) => {
  const s = n ? { ...n, direction: "backward" } : { direction: "backward" };
  return Js(t)(e, r, s);
}, Cf = (t) => async (e, r, n) => Js(t)(e, r, n), Of = /^[cC][0-9a-z]{6,}$/, Af = /^[0-9a-z]+$/, Nf = /^[0-9A-HJKMNP-TV-Za-hjkmnp-tv-z]{26}$/, xf = /^[0-9a-vA-V]{20}$/, zf = /^[A-Za-z0-9]{27}$/, jf = /^[a-zA-Z0-9_-]{21}$/, Mf = /^P(?:(\d+W)|(?!.*W)(?=\d|T\d)(\d+Y)?(\d+M)?(\d+D)?(T(?=\d)(\d+H)?(\d+M)?(\d+([.,]\d+)?S)?)?)$/, qf = /^([0-9a-fA-F]{8}-[0-9a-fA-F]{4}-[0-9a-fA-F]{4}-[0-9a-fA-F]{4}-[0-9a-fA-F]{12})$/, Ia = (t) => t ? new RegExp(`^([0-9a-fA-F]{8}-[0-9a-fA-F]{4}-${t}[0-9a-fA-F]{3}-[89abAB][0-9a-fA-F]{3}-[0-9a-fA-F]{12})$`) : /^([0-9a-fA-F]{8}-[0-9a-fA-F]{4}-[1-8][0-9a-fA-F]{3}-[89abAB][0-9a-fA-F]{3}-[0-9a-fA-F]{12}|00000000-0000-0000-0000-000000000000|ffffffff-ffff-ffff-ffff-ffffffffffff)$/, Uf = /^(?!\.)(?!.*\.\.)([A-Za-z0-9_'+\-\.]*)[A-Za-z0-9_+-]@([A-Za-z0-9][A-Za-z0-9\-]*\.)+[A-Za-z]{2,}$/, Df = "^(\\p{Extended_Pictographic}|\\p{Emoji_Component})+$";
function Lf() {
  return new RegExp(Df, "u");
}
const Zf = /^(?:(?:25[0-5]|2[0-4][0-9]|1[0-9][0-9]|[1-9][0-9]|[0-9])\.){3}(?:25[0-5]|2[0-4][0-9]|1[0-9][0-9]|[1-9][0-9]|[0-9])$/, Hf = /^(([0-9a-fA-F]{1,4}:){7}[0-9a-fA-F]{1,4}|([0-9a-fA-F]{1,4}:){1,7}:|([0-9a-fA-F]{1,4}:){1,6}:[0-9a-fA-F]{1,4}|([0-9a-fA-F]{1,4}:){1,5}(:[0-9a-fA-F]{1,4}){1,2}|([0-9a-fA-F]{1,4}:){1,4}(:[0-9a-fA-F]{1,4}){1,3}|([0-9a-fA-F]{1,4}:){1,3}(:[0-9a-fA-F]{1,4}){1,4}|([0-9a-fA-F]{1,4}:){1,2}(:[0-9a-fA-F]{1,4}){1,5}|[0-9a-fA-F]{1,4}:((:[0-9a-fA-F]{1,4}){1,6})|:((:[0-9a-fA-F]{1,4}){1,7}|:))$/, Ff = /^((25[0-5]|2[0-4][0-9]|1[0-9][0-9]|[1-9][0-9]|[0-9])\.){3}(25[0-5]|2[0-4][0-9]|1[0-9][0-9]|[1-9][0-9]|[0-9])\/([0-9]|[1-2][0-9]|3[0-2])$/, Vf = /^(([0-9a-fA-F]{1,4}:){7}[0-9a-fA-F]{1,4}|::|([0-9a-fA-F]{1,4})?::([0-9a-fA-F]{1,4}:?){0,6})\/(12[0-8]|1[01][0-9]|[1-9]?[0-9])$/, Bf = /^$|^(?:[0-9a-zA-Z+/]{4})*(?:(?:[0-9a-zA-Z+/]{2}==)|(?:[0-9a-zA-Z+/]{3}=))?$/, kl = /^[A-Za-z0-9_-]*$/, Wf = /^https?$/, Gf = /^\+[1-9]\d{6,14}$/, $l = "(?:(?:\\d\\d[2468][048]|\\d\\d[13579][26]|\\d\\d0[48]|[02468][048]00|[13579][26]00)-02-29|\\d{4}-(?:(?:0[13578]|1[02])-(?:0[1-9]|[12]\\d|3[01])|(?:0[469]|11)-(?:0[1-9]|[12]\\d|30)|(?:02)-(?:0[1-9]|1\\d|2[0-8])))", Jf = /* @__PURE__ */ new RegExp(`^${$l}$`);
function El(t) {
  const e = "(?:[01]\\d|2[0-3]):[0-5]\\d";
  return typeof t.precision == "number" ? t.precision === -1 ? `${e}` : t.precision === 0 ? `${e}:[0-5]\\d` : `${e}:[0-5]\\d\\.\\d{${t.precision}}` : `${e}(?::[0-5]\\d(?:\\.\\d+)?)?`;
}
function Kf(t) {
  return new RegExp(`^${El(t)}$`);
}
function Qf(t) {
  const e = El({ precision: t.precision }), r = ["Z"];
  t.local && r.push(""), t.offset && r.push("([+-](?:[01]\\d|2[0-3]):[0-5]\\d)");
  const n = `${e}(?:${r.join("|")})`;
  return new RegExp(`^${$l}T(?:${n})$`);
}
const Yf = (t) => {
  const e = t ? `[\\s\\S]{${t?.minimum ?? 0},${t?.maximum ?? ""}}` : "[\\s\\S]*";
  return new RegExp(`^${e}$`);
}, Xf = /^-?\d+$/, Tl = /^-?\d+(?:\.\d+)?$/, ep = /^(?:true|false)$/i, tp = /^null$/i, rp = /^[^A-Z]*$/, np = /^[^a-z]*$/, tt = /* @__PURE__ */ M("$ZodCheck", (t, e) => {
  var r;
  t._zod ?? (t._zod = {}), t._zod.def = e, (r = t._zod).onattach ?? (r.onattach = []);
}), Rl = {
  number: "number",
  bigint: "bigint",
  object: "date"
}, Il = /* @__PURE__ */ M("$ZodCheckLessThan", (t, e) => {
  tt.init(t, e);
  const r = Rl[typeof e.value];
  t._zod.onattach.push((n) => {
    const s = n._zod.bag, i = (e.inclusive ? s.maximum : s.exclusiveMaximum) ?? Number.POSITIVE_INFINITY;
    e.value < i && (e.inclusive ? s.maximum = e.value : s.exclusiveMaximum = e.value);
  }), t._zod.check = (n) => {
    (e.inclusive ? n.value <= e.value : n.value < e.value) || n.issues.push({
      origin: r,
      code: "too_big",
      maximum: typeof e.value == "object" ? e.value.getTime() : e.value,
      input: n.value,
      inclusive: e.inclusive,
      inst: t,
      continue: !e.abort
    });
  };
}), Pl = /* @__PURE__ */ M("$ZodCheckGreaterThan", (t, e) => {
  tt.init(t, e);
  const r = Rl[typeof e.value];
  t._zod.onattach.push((n) => {
    const s = n._zod.bag, i = (e.inclusive ? s.minimum : s.exclusiveMinimum) ?? Number.NEGATIVE_INFINITY;
    e.value > i && (e.inclusive ? s.minimum = e.value : s.exclusiveMinimum = e.value);
  }), t._zod.check = (n) => {
    (e.inclusive ? n.value >= e.value : n.value > e.value) || n.issues.push({
      origin: r,
      code: "too_small",
      minimum: typeof e.value == "object" ? e.value.getTime() : e.value,
      input: n.value,
      inclusive: e.inclusive,
      inst: t,
      continue: !e.abort
    });
  };
}), sp = /* @__PURE__ */ M("$ZodCheckMultipleOf", (t, e) => {
  tt.init(t, e), t._zod.onattach.push((r) => {
    var n;
    (n = r._zod.bag).multipleOf ?? (n.multipleOf = e.value);
  }), t._zod.check = (r) => {
    if (typeof r.value != typeof e.value)
      throw new Error("Cannot mix number and bigint in multiple_of check.");
    (typeof r.value == "bigint" ? r.value % e.value === BigInt(0) : sf(r.value, e.value) === 0) || r.issues.push({
      origin: typeof r.value,
      code: "not_multiple_of",
      divisor: e.value,
      input: r.value,
      inst: t,
      continue: !e.abort
    });
  };
}), ip = /* @__PURE__ */ M("$ZodCheckNumberFormat", (t, e) => {
  tt.init(t, e), e.format = e.format || "float64";
  const r = e.format?.includes("int"), n = r ? "int" : "number", [s, i] = lf[e.format];
  t._zod.onattach.push((o) => {
    const a = o._zod.bag;
    a.format = e.format, a.minimum = s, a.maximum = i, r && (a.pattern = Xf);
  }), t._zod.check = (o) => {
    const a = o.value;
    if (r) {
      if (!Number.isInteger(a)) {
        o.issues.push({
          expected: n,
          format: e.format,
          code: "invalid_type",
          continue: !1,
          input: a,
          inst: t
        });
        return;
      }
      if (!Number.isSafeInteger(a)) {
        a > 0 ? o.issues.push({
          input: a,
          code: "too_big",
          maximum: Number.MAX_SAFE_INTEGER,
          note: "Integers must be within the safe integer range.",
          inst: t,
          origin: n,
          inclusive: !0,
          continue: !e.abort
        }) : o.issues.push({
          input: a,
          code: "too_small",
          minimum: Number.MIN_SAFE_INTEGER,
          note: "Integers must be within the safe integer range.",
          inst: t,
          origin: n,
          inclusive: !0,
          continue: !e.abort
        });
        return;
      }
    }
    a < s && o.issues.push({
      origin: "number",
      input: a,
      code: "too_small",
      minimum: s,
      inclusive: !0,
      inst: t,
      continue: !e.abort
    }), a > i && o.issues.push({
      origin: "number",
      input: a,
      code: "too_big",
      maximum: i,
      inclusive: !0,
      inst: t,
      continue: !e.abort
    });
  };
}), op = /* @__PURE__ */ M("$ZodCheckMaxLength", (t, e) => {
  var r;
  tt.init(t, e), (r = t._zod.def).when ?? (r.when = (n) => {
    const s = n.value;
    return !Ao(s) && s.length !== void 0;
  }), t._zod.onattach.push((n) => {
    const s = n._zod.bag.maximum ?? Number.POSITIVE_INFINITY;
    e.maximum < s && (n._zod.bag.maximum = e.maximum);
  }), t._zod.check = (n) => {
    const s = n.value;
    if (s.length <= e.maximum)
      return;
    const o = xo(s);
    n.issues.push({
      origin: o,
      code: "too_big",
      maximum: e.maximum,
      inclusive: !0,
      input: s,
      inst: t,
      continue: !e.abort
    });
  };
}), ap = /* @__PURE__ */ M("$ZodCheckMinLength", (t, e) => {
  var r;
  tt.init(t, e), (r = t._zod.def).when ?? (r.when = (n) => {
    const s = n.value;
    return !Ao(s) && s.length !== void 0;
  }), t._zod.onattach.push((n) => {
    const s = n._zod.bag.minimum ?? Number.NEGATIVE_INFINITY;
    e.minimum > s && (n._zod.bag.minimum = e.minimum);
  }), t._zod.check = (n) => {
    const s = n.value;
    if (s.length >= e.minimum)
      return;
    const o = xo(s);
    n.issues.push({
      origin: o,
      code: "too_small",
      minimum: e.minimum,
      inclusive: !0,
      input: s,
      inst: t,
      continue: !e.abort
    });
  };
}), cp = /* @__PURE__ */ M("$ZodCheckLengthEquals", (t, e) => {
  var r;
  tt.init(t, e), (r = t._zod.def).when ?? (r.when = (n) => {
    const s = n.value;
    return !Ao(s) && s.length !== void 0;
  }), t._zod.onattach.push((n) => {
    const s = n._zod.bag;
    s.minimum = e.length, s.maximum = e.length, s.length = e.length;
  }), t._zod.check = (n) => {
    const s = n.value, i = s.length;
    if (i === e.length)
      return;
    const o = xo(s), a = i > e.length;
    n.issues.push({
      origin: o,
      ...a ? { code: "too_big", maximum: e.length } : { code: "too_small", minimum: e.length },
      inclusive: !0,
      exact: !0,
      input: n.value,
      inst: t,
      continue: !e.abort
    });
  };
}), Ks = /* @__PURE__ */ M("$ZodCheckStringFormat", (t, e) => {
  var r, n;
  tt.init(t, e), t._zod.onattach.push((s) => {
    const i = s._zod.bag;
    i.format = e.format, e.pattern && (i.patterns ?? (i.patterns = /* @__PURE__ */ new Set()), i.patterns.add(e.pattern));
  }), e.pattern ? (r = t._zod).check ?? (r.check = (s) => {
    e.pattern.lastIndex = 0, !e.pattern.test(s.value) && s.issues.push({
      origin: "string",
      code: "invalid_format",
      format: e.format,
      input: s.value,
      ...e.pattern ? { pattern: e.pattern.toString() } : {},
      inst: t,
      continue: !e.abort
    });
  }) : (n = t._zod).check ?? (n.check = () => {
  });
}), up = /* @__PURE__ */ M("$ZodCheckRegex", (t, e) => {
  Ks.init(t, e), t._zod.check = (r) => {
    e.pattern.lastIndex = 0, !e.pattern.test(r.value) && r.issues.push({
      origin: "string",
      code: "invalid_format",
      format: "regex",
      input: r.value,
      pattern: e.pattern.toString(),
      inst: t,
      continue: !e.abort
    });
  };
}), lp = /* @__PURE__ */ M("$ZodCheckLowerCase", (t, e) => {
  e.pattern ?? (e.pattern = rp), Ks.init(t, e);
}), dp = /* @__PURE__ */ M("$ZodCheckUpperCase", (t, e) => {
  e.pattern ?? (e.pattern = np), Ks.init(t, e);
}), hp = /* @__PURE__ */ M("$ZodCheckIncludes", (t, e) => {
  tt.init(t, e);
  const r = kr(e.includes), n = new RegExp(typeof e.position == "number" ? `^.{${e.position}}${r}` : r);
  e.pattern = n, t._zod.onattach.push((s) => {
    const i = s._zod.bag;
    i.patterns ?? (i.patterns = /* @__PURE__ */ new Set()), i.patterns.add(n);
  }), t._zod.check = (s) => {
    s.value.includes(e.includes, e.position) || s.issues.push({
      origin: "string",
      code: "invalid_format",
      format: "includes",
      includes: e.includes,
      input: s.value,
      inst: t,
      continue: !e.abort
    });
  };
}), fp = /* @__PURE__ */ M("$ZodCheckStartsWith", (t, e) => {
  tt.init(t, e);
  const r = new RegExp(`^${kr(e.prefix)}.*`);
  e.pattern ?? (e.pattern = r), t._zod.onattach.push((n) => {
    const s = n._zod.bag;
    s.patterns ?? (s.patterns = /* @__PURE__ */ new Set()), s.patterns.add(r);
  }), t._zod.check = (n) => {
    n.value.startsWith(e.prefix) || n.issues.push({
      origin: "string",
      code: "invalid_format",
      format: "starts_with",
      prefix: e.prefix,
      input: n.value,
      inst: t,
      continue: !e.abort
    });
  };
}), pp = /* @__PURE__ */ M("$ZodCheckEndsWith", (t, e) => {
  tt.init(t, e);
  const r = new RegExp(`.*${kr(e.suffix)}$`);
  e.pattern ?? (e.pattern = r), t._zod.onattach.push((n) => {
    const s = n._zod.bag;
    s.patterns ?? (s.patterns = /* @__PURE__ */ new Set()), s.patterns.add(r);
  }), t._zod.check = (n) => {
    n.value.endsWith(e.suffix) || n.issues.push({
      origin: "string",
      code: "invalid_format",
      format: "ends_with",
      suffix: e.suffix,
      input: n.value,
      inst: t,
      continue: !e.abort
    });
  };
}), mp = /* @__PURE__ */ M("$ZodCheckOverwrite", (t, e) => {
  tt.init(t, e), t._zod.check = (r) => {
    r.value = e.tx(r.value);
  };
});
class gp {
  constructor(e = []) {
    this.content = [], this.indent = 0, this && (this.args = e);
  }
  indented(e) {
    this.indent += 1, e(this), this.indent -= 1;
  }
  write(e) {
    if (typeof e == "function") {
      e(this, { execution: "sync" }), e(this, { execution: "async" });
      return;
    }
    const n = e.split(`
`).filter((o) => o), s = Math.min(...n.map((o) => o.length - o.trimStart().length)), i = n.map((o) => o.slice(s)).map((o) => " ".repeat(this.indent * 2) + o);
    for (const o of i)
      this.content.push(o);
  }
  compile() {
    const e = Function, r = this?.args, s = [...(this?.content ?? [""]).map((i) => `  ${i}`)];
    return new e(...r, s.join(`
`));
  }
}
const _p = {
  major: 4,
  minor: 4,
  patch: 3
}, Re = /* @__PURE__ */ M("$ZodType", (t, e) => {
  var r;
  t ?? (t = {}), t._zod.def = e, t._zod.bag = t._zod.bag || {}, t._zod.version = _p;
  const n = [...t._zod.def.checks ?? []];
  t._zod.traits.has("$ZodCheck") && n.unshift(t);
  for (const s of n)
    for (const i of s._zod.onattach)
      i(t);
  if (n.length === 0)
    (r = t._zod).deferred ?? (r.deferred = []), t._zod.deferred?.push(() => {
      t._zod.run = t._zod.parse;
    });
  else {
    const s = (o, a, c) => {
      let u = pr(o), l;
      for (const h of a) {
        if (h._zod.def.when) {
          if (yf(o) || !h._zod.def.when(o))
            continue;
        } else if (u)
          continue;
        const p = o.issues.length, g = h._zod.check(o);
        if (g instanceof Promise && c?.async === !1)
          throw new br();
        if (l || g instanceof Promise)
          l = (l ?? Promise.resolve()).then(async () => {
            await g, o.issues.length !== p && (u || (u = pr(o, p)));
          });
        else {
          if (o.issues.length === p)
            continue;
          u || (u = pr(o, p));
        }
      }
      return l ? l.then(() => o) : o;
    }, i = (o, a, c) => {
      if (pr(o))
        return o.aborted = !0, o;
      const u = s(a, n, c);
      if (u instanceof Promise) {
        if (c.async === !1)
          throw new br();
        return u.then((l) => t._zod.parse(l, c));
      }
      return t._zod.parse(u, c);
    };
    t._zod.run = (o, a) => {
      if (a.skipChecks)
        return t._zod.parse(o, a);
      if (a.direction === "backward") {
        const u = t._zod.parse({ value: o.value, issues: [] }, { ...a, skipChecks: !0 });
        return u instanceof Promise ? u.then((l) => i(l, o, a)) : i(u, o, a);
      }
      const c = t._zod.parse(o, a);
      if (c instanceof Promise) {
        if (a.async === !1)
          throw new br();
        return c.then((u) => s(u, n, a));
      }
      return s(c, n, a);
    };
  }
  Se(t, "~standard", () => ({
    validate: (s) => {
      try {
        const i = zo(t, s);
        return i.success ? { value: i.data } : { issues: i.error?.issues };
      } catch {
        return jo(t, s).then((o) => o.success ? { value: o.data } : { issues: o.error?.issues });
      }
    },
    vendor: "zod",
    version: 1
  }));
}), Mo = /* @__PURE__ */ M("$ZodString", (t, e) => {
  Re.init(t, e), t._zod.pattern = [...t?._zod.bag?.patterns ?? []].pop() ?? Yf(t._zod.bag), t._zod.parse = (r, n) => {
    if (e.coerce)
      try {
        r.value = String(r.value);
      } catch {
      }
    return typeof r.value == "string" || r.issues.push({
      expected: "string",
      code: "invalid_type",
      input: r.value,
      inst: t
    }), r;
  };
}), Ae = /* @__PURE__ */ M("$ZodStringFormat", (t, e) => {
  Ks.init(t, e), Mo.init(t, e);
}), yp = /* @__PURE__ */ M("$ZodGUID", (t, e) => {
  e.pattern ?? (e.pattern = qf), Ae.init(t, e);
}), wp = /* @__PURE__ */ M("$ZodUUID", (t, e) => {
  if (e.version) {
    const n = {
      v1: 1,
      v2: 2,
      v3: 3,
      v4: 4,
      v5: 5,
      v6: 6,
      v7: 7,
      v8: 8
    }[e.version];
    if (n === void 0)
      throw new Error(`Invalid UUID version: "${e.version}"`);
    e.pattern ?? (e.pattern = Ia(n));
  } else
    e.pattern ?? (e.pattern = Ia());
  Ae.init(t, e);
}), vp = /* @__PURE__ */ M("$ZodEmail", (t, e) => {
  e.pattern ?? (e.pattern = Uf), Ae.init(t, e);
}), bp = /* @__PURE__ */ M("$ZodURL", (t, e) => {
  Ae.init(t, e), t._zod.check = (r) => {
    try {
      const n = r.value.trim();
      if (!e.normalize && e.protocol?.source === Wf.source && !/^https?:\/\//i.test(n)) {
        r.issues.push({
          code: "invalid_format",
          format: "url",
          note: "Invalid URL format",
          input: r.value,
          inst: t,
          continue: !e.abort
        });
        return;
      }
      const s = new URL(n);
      e.hostname && (e.hostname.lastIndex = 0, e.hostname.test(s.hostname) || r.issues.push({
        code: "invalid_format",
        format: "url",
        note: "Invalid hostname",
        pattern: e.hostname.source,
        input: r.value,
        inst: t,
        continue: !e.abort
      })), e.protocol && (e.protocol.lastIndex = 0, e.protocol.test(s.protocol.endsWith(":") ? s.protocol.slice(0, -1) : s.protocol) || r.issues.push({
        code: "invalid_format",
        format: "url",
        note: "Invalid protocol",
        pattern: e.protocol.source,
        input: r.value,
        inst: t,
        continue: !e.abort
      })), e.normalize ? r.value = s.href : r.value = n;
      return;
    } catch {
      r.issues.push({
        code: "invalid_format",
        format: "url",
        input: r.value,
        inst: t,
        continue: !e.abort
      });
    }
  };
}), Sp = /* @__PURE__ */ M("$ZodEmoji", (t, e) => {
  e.pattern ?? (e.pattern = Lf()), Ae.init(t, e);
}), kp = /* @__PURE__ */ M("$ZodNanoID", (t, e) => {
  e.pattern ?? (e.pattern = jf), Ae.init(t, e);
}), $p = /* @__PURE__ */ M("$ZodCUID", (t, e) => {
  e.pattern ?? (e.pattern = Of), Ae.init(t, e);
}), Ep = /* @__PURE__ */ M("$ZodCUID2", (t, e) => {
  e.pattern ?? (e.pattern = Af), Ae.init(t, e);
}), Tp = /* @__PURE__ */ M("$ZodULID", (t, e) => {
  e.pattern ?? (e.pattern = Nf), Ae.init(t, e);
}), Rp = /* @__PURE__ */ M("$ZodXID", (t, e) => {
  e.pattern ?? (e.pattern = xf), Ae.init(t, e);
}), Ip = /* @__PURE__ */ M("$ZodKSUID", (t, e) => {
  e.pattern ?? (e.pattern = zf), Ae.init(t, e);
}), Pp = /* @__PURE__ */ M("$ZodISODateTime", (t, e) => {
  e.pattern ?? (e.pattern = Qf(e)), Ae.init(t, e);
}), Cp = /* @__PURE__ */ M("$ZodISODate", (t, e) => {
  e.pattern ?? (e.pattern = Jf), Ae.init(t, e);
}), Op = /* @__PURE__ */ M("$ZodISOTime", (t, e) => {
  e.pattern ?? (e.pattern = Kf(e)), Ae.init(t, e);
}), Ap = /* @__PURE__ */ M("$ZodISODuration", (t, e) => {
  e.pattern ?? (e.pattern = Mf), Ae.init(t, e);
}), Np = /* @__PURE__ */ M("$ZodIPv4", (t, e) => {
  e.pattern ?? (e.pattern = Zf), Ae.init(t, e), t._zod.bag.format = "ipv4";
}), xp = /* @__PURE__ */ M("$ZodIPv6", (t, e) => {
  e.pattern ?? (e.pattern = Hf), Ae.init(t, e), t._zod.bag.format = "ipv6", t._zod.check = (r) => {
    try {
      new URL(`http://[${r.value}]`);
    } catch {
      r.issues.push({
        code: "invalid_format",
        format: "ipv6",
        input: r.value,
        inst: t,
        continue: !e.abort
      });
    }
  };
}), zp = /* @__PURE__ */ M("$ZodCIDRv4", (t, e) => {
  e.pattern ?? (e.pattern = Ff), Ae.init(t, e);
}), jp = /* @__PURE__ */ M("$ZodCIDRv6", (t, e) => {
  e.pattern ?? (e.pattern = Vf), Ae.init(t, e), t._zod.check = (r) => {
    const n = r.value.split("/");
    try {
      if (n.length !== 2)
        throw new Error();
      const [s, i] = n;
      if (!i)
        throw new Error();
      const o = Number(i);
      if (`${o}` !== i)
        throw new Error();
      if (o < 0 || o > 128)
        throw new Error();
      new URL(`http://[${s}]`);
    } catch {
      r.issues.push({
        code: "invalid_format",
        format: "cidrv6",
        input: r.value,
        inst: t,
        continue: !e.abort
      });
    }
  };
});
function Cl(t) {
  if (t === "")
    return !0;
  if (/\s/.test(t) || t.length % 4 !== 0)
    return !1;
  try {
    return atob(t), !0;
  } catch {
    return !1;
  }
}
const Mp = /* @__PURE__ */ M("$ZodBase64", (t, e) => {
  e.pattern ?? (e.pattern = Bf), Ae.init(t, e), t._zod.bag.contentEncoding = "base64", t._zod.check = (r) => {
    Cl(r.value) || r.issues.push({
      code: "invalid_format",
      format: "base64",
      input: r.value,
      inst: t,
      continue: !e.abort
    });
  };
});
function qp(t) {
  if (!kl.test(t))
    return !1;
  const e = t.replace(/[-_]/g, (n) => n === "-" ? "+" : "/"), r = e.padEnd(Math.ceil(e.length / 4) * 4, "=");
  return Cl(r);
}
const Up = /* @__PURE__ */ M("$ZodBase64URL", (t, e) => {
  e.pattern ?? (e.pattern = kl), Ae.init(t, e), t._zod.bag.contentEncoding = "base64url", t._zod.check = (r) => {
    qp(r.value) || r.issues.push({
      code: "invalid_format",
      format: "base64url",
      input: r.value,
      inst: t,
      continue: !e.abort
    });
  };
}), Dp = /* @__PURE__ */ M("$ZodE164", (t, e) => {
  e.pattern ?? (e.pattern = Gf), Ae.init(t, e);
});
function Lp(t, e = null) {
  try {
    const r = t.split(".");
    if (r.length !== 3)
      return !1;
    const [n] = r;
    if (!n)
      return !1;
    const s = JSON.parse(atob(n));
    return !("typ" in s && s?.typ !== "JWT" || !s.alg || e && (!("alg" in s) || s.alg !== e));
  } catch {
    return !1;
  }
}
const Zp = /* @__PURE__ */ M("$ZodJWT", (t, e) => {
  Ae.init(t, e), t._zod.check = (r) => {
    Lp(r.value, e.alg) || r.issues.push({
      code: "invalid_format",
      format: "jwt",
      input: r.value,
      inst: t,
      continue: !e.abort
    });
  };
}), Ol = /* @__PURE__ */ M("$ZodNumber", (t, e) => {
  Re.init(t, e), t._zod.pattern = t._zod.bag.pattern ?? Tl, t._zod.parse = (r, n) => {
    if (e.coerce)
      try {
        r.value = Number(r.value);
      } catch {
      }
    const s = r.value;
    if (typeof s == "number" && !Number.isNaN(s) && Number.isFinite(s))
      return r;
    const i = typeof s == "number" ? Number.isNaN(s) ? "NaN" : Number.isFinite(s) ? void 0 : "Infinity" : void 0;
    return r.issues.push({
      expected: "number",
      code: "invalid_type",
      input: s,
      inst: t,
      ...i ? { received: i } : {}
    }), r;
  };
}), Hp = /* @__PURE__ */ M("$ZodNumberFormat", (t, e) => {
  ip.init(t, e), Ol.init(t, e);
}), Fp = /* @__PURE__ */ M("$ZodBoolean", (t, e) => {
  Re.init(t, e), t._zod.pattern = ep, t._zod.parse = (r, n) => {
    if (e.coerce)
      try {
        r.value = !!r.value;
      } catch {
      }
    const s = r.value;
    return typeof s == "boolean" || r.issues.push({
      expected: "boolean",
      code: "invalid_type",
      input: s,
      inst: t
    }), r;
  };
}), Vp = /* @__PURE__ */ M("$ZodNull", (t, e) => {
  Re.init(t, e), t._zod.pattern = tp, t._zod.values = /* @__PURE__ */ new Set([null]), t._zod.parse = (r, n) => {
    const s = r.value;
    return s === null || r.issues.push({
      expected: "null",
      code: "invalid_type",
      input: s,
      inst: t
    }), r;
  };
}), Bp = /* @__PURE__ */ M("$ZodAny", (t, e) => {
  Re.init(t, e), t._zod.parse = (r) => r;
}), Wp = /* @__PURE__ */ M("$ZodUnknown", (t, e) => {
  Re.init(t, e), t._zod.parse = (r) => r;
}), Gp = /* @__PURE__ */ M("$ZodNever", (t, e) => {
  Re.init(t, e), t._zod.parse = (r, n) => (r.issues.push({
    expected: "never",
    code: "invalid_type",
    input: r.value,
    inst: t
  }), r);
});
function Pa(t, e, r) {
  t.issues.length && e.issues.push(...mr(r, t.issues)), e.value[r] = t.value;
}
const Jp = /* @__PURE__ */ M("$ZodArray", (t, e) => {
  Re.init(t, e), t._zod.parse = (r, n) => {
    const s = r.value;
    if (!Array.isArray(s))
      return r.issues.push({
        expected: "array",
        code: "invalid_type",
        input: s,
        inst: t
      }), r;
    r.value = Array(s.length);
    const i = [];
    for (let o = 0; o < s.length; o++) {
      const a = s[o], c = e.element._zod.run({
        value: a,
        issues: []
      }, n);
      c instanceof Promise ? i.push(c.then((u) => Pa(u, r, o))) : Pa(c, r, o);
    }
    return i.length ? Promise.all(i).then(() => r) : r;
  };
});
function ws(t, e, r, n, s, i) {
  const o = r in n;
  if (t.issues.length) {
    if (s && i && !o)
      return;
    e.issues.push(...mr(r, t.issues));
  }
  if (!o && !s) {
    t.issues.length || e.issues.push({
      code: "invalid_type",
      expected: "nonoptional",
      input: void 0,
      path: [r]
    });
    return;
  }
  t.value === void 0 ? o && (e.value[r] = void 0) : e.value[r] = t.value;
}
function Al(t) {
  const e = Object.keys(t.shape);
  for (const n of e)
    if (!t.shape?.[n]?._zod?.traits?.has("$ZodType"))
      throw new Error(`Invalid element at key "${n}": expected a Zod schema`);
  const r = uf(t.shape);
  return {
    ...t,
    keys: e,
    keySet: new Set(e),
    numKeys: e.length,
    optionalKeys: new Set(r)
  };
}
function Nl(t, e, r, n, s, i) {
  const o = [], a = s.keySet, c = s.catchall._zod, u = c.def.type, l = c.optin === "optional", h = c.optout === "optional";
  for (const p in e) {
    if (p === "__proto__" || a.has(p))
      continue;
    if (u === "never") {
      o.push(p);
      continue;
    }
    const g = c.run({ value: e[p], issues: [] }, n);
    g instanceof Promise ? t.push(g.then((S) => ws(S, r, p, e, l, h))) : ws(g, r, p, e, l, h);
  }
  return o.length && r.issues.push({
    code: "unrecognized_keys",
    keys: o,
    input: e,
    inst: i
  }), t.length ? Promise.all(t).then(() => r) : r;
}
const xl = /* @__PURE__ */ M("$ZodObject", (t, e) => {
  if (Re.init(t, e), !Object.getOwnPropertyDescriptor(e, "shape")?.get) {
    const a = e.shape;
    Object.defineProperty(e, "shape", {
      get: () => {
        const c = { ...a };
        return Object.defineProperty(e, "shape", {
          value: c
        }), c;
      }
    });
  }
  const n = Fs(() => Al(e));
  Se(t._zod, "propValues", () => {
    const a = e.shape, c = {};
    for (const u in a) {
      const l = a[u]._zod;
      if (l.values) {
        c[u] ?? (c[u] = /* @__PURE__ */ new Set());
        for (const h of l.values)
          c[u].add(h);
      }
    }
    return c;
  });
  const s = Xr, i = e.catchall;
  let o;
  t._zod.parse = (a, c) => {
    o ?? (o = n.value);
    const u = a.value;
    if (!s(u))
      return a.issues.push({
        expected: "object",
        code: "invalid_type",
        input: u,
        inst: t
      }), a;
    a.value = {};
    const l = [], h = o.shape;
    for (const p of o.keys) {
      const g = h[p], S = g._zod.optin === "optional", k = g._zod.optout === "optional", y = g._zod.run({ value: u[p], issues: [] }, c);
      y instanceof Promise ? l.push(y.then((b) => ws(b, a, p, u, S, k))) : ws(y, a, p, u, S, k);
    }
    return i ? Nl(l, u, a, c, n.value, t) : l.length ? Promise.all(l).then(() => a) : a;
  };
}), Kp = /* @__PURE__ */ M("$ZodObjectJIT", (t, e) => {
  xl.init(t, e);
  const r = t._zod.parse, n = Fs(() => Al(e)), s = (p) => {
    const g = new gp(["shape", "payload", "ctx"]), S = n.value, k = (w) => {
      const $ = Ra(w);
      return `shape[${$}]._zod.run({ value: input[${$}], issues: [] }, ctx)`;
    };
    g.write("const input = payload.value;");
    const y = /* @__PURE__ */ Object.create(null);
    let b = 0;
    for (const w of S.keys)
      y[w] = `key_${b++}`;
    g.write("const newResult = {};");
    for (const w of S.keys) {
      const $ = y[w], d = Ra(w), f = p[w], _ = f?._zod?.optin === "optional", E = f?._zod?.optout === "optional";
      g.write(`const ${$} = ${k(w)};`), _ && E ? g.write(`
        if (${$}.issues.length) {
          if (${d} in input) {
            payload.issues = payload.issues.concat(${$}.issues.map(iss => ({
              ...iss,
              path: iss.path ? [${d}, ...iss.path] : [${d}]
            })));
          }
        }
        
        if (${$}.value === undefined) {
          if (${d} in input) {
            newResult[${d}] = undefined;
          }
        } else {
          newResult[${d}] = ${$}.value;
        }
        
      `) : _ ? g.write(`
        if (${$}.issues.length) {
          payload.issues = payload.issues.concat(${$}.issues.map(iss => ({
            ...iss,
            path: iss.path ? [${d}, ...iss.path] : [${d}]
          })));
        }
        
        if (${$}.value === undefined) {
          if (${d} in input) {
            newResult[${d}] = undefined;
          }
        } else {
          newResult[${d}] = ${$}.value;
        }
        
      `) : g.write(`
        const ${$}_present = ${d} in input;
        if (${$}.issues.length) {
          payload.issues = payload.issues.concat(${$}.issues.map(iss => ({
            ...iss,
            path: iss.path ? [${d}, ...iss.path] : [${d}]
          })));
        }
        if (!${$}_present && !${$}.issues.length) {
          payload.issues.push({
            code: "invalid_type",
            expected: "nonoptional",
            input: undefined,
            path: [${d}]
          });
        }

        if (${$}_present) {
          if (${$}.value === undefined) {
            newResult[${d}] = undefined;
          } else {
            newResult[${d}] = ${$}.value;
          }
        }

      `);
    }
    g.write("payload.value = newResult;"), g.write("return payload;");
    const m = g.compile();
    return (w, $) => m(p, w, $);
  };
  let i;
  const o = Xr, a = !Oo.jitless, u = a && af.value, l = e.catchall;
  let h;
  t._zod.parse = (p, g) => {
    h ?? (h = n.value);
    const S = p.value;
    return o(S) ? a && u && g?.async === !1 && g.jitless !== !0 ? (i || (i = s(e.shape)), p = i(p, g), l ? Nl([], S, p, g, h, t) : p) : r(p, g) : (p.issues.push({
      expected: "object",
      code: "invalid_type",
      input: S,
      inst: t
    }), p);
  };
});
function Ca(t, e, r, n) {
  for (const i of t)
    if (i.issues.length === 0)
      return e.value = i.value, e;
  const s = t.filter((i) => !pr(i));
  return s.length === 1 ? (e.value = s[0].value, s[0]) : (e.issues.push({
    code: "invalid_union",
    input: e.value,
    inst: r,
    errors: t.map((i) => i.issues.map((o) => Ct(o, n, Pt())))
  }), e);
}
const zl = /* @__PURE__ */ M("$ZodUnion", (t, e) => {
  Re.init(t, e), Se(t._zod, "optin", () => e.options.some((n) => n._zod.optin === "optional") ? "optional" : void 0), Se(t._zod, "optout", () => e.options.some((n) => n._zod.optout === "optional") ? "optional" : void 0), Se(t._zod, "values", () => {
    if (e.options.every((n) => n._zod.values))
      return new Set(e.options.flatMap((n) => Array.from(n._zod.values)));
  }), Se(t._zod, "pattern", () => {
    if (e.options.every((n) => n._zod.pattern)) {
      const n = e.options.map((s) => s._zod.pattern);
      return new RegExp(`^(${n.map((s) => No(s.source)).join("|")})$`);
    }
  });
  const r = e.options.length === 1 ? e.options[0]._zod.run : null;
  t._zod.parse = (n, s) => {
    if (r)
      return r(n, s);
    let i = !1;
    const o = [];
    for (const a of e.options) {
      const c = a._zod.run({
        value: n.value,
        issues: []
      }, s);
      if (c instanceof Promise)
        o.push(c), i = !0;
      else {
        if (c.issues.length === 0)
          return c;
        o.push(c);
      }
    }
    return i ? Promise.all(o).then((a) => Ca(a, n, t, s)) : Ca(o, n, t, s);
  };
}), Qp = /* @__PURE__ */ M("$ZodDiscriminatedUnion", (t, e) => {
  e.inclusive = !1, zl.init(t, e);
  const r = t._zod.parse;
  Se(t._zod, "propValues", () => {
    const s = {};
    for (const i of e.options) {
      const o = i._zod.propValues;
      if (!o || Object.keys(o).length === 0)
        throw new Error(`Invalid discriminated union option at index "${e.options.indexOf(i)}"`);
      for (const [a, c] of Object.entries(o)) {
        s[a] || (s[a] = /* @__PURE__ */ new Set());
        for (const u of c)
          s[a].add(u);
      }
    }
    return s;
  });
  const n = Fs(() => {
    const s = e.options, i = /* @__PURE__ */ new Map();
    for (const o of s) {
      const a = o._zod.propValues?.[e.discriminator];
      if (!a || a.size === 0)
        throw new Error(`Invalid discriminated union option at index "${e.options.indexOf(o)}"`);
      for (const c of a) {
        if (i.has(c))
          throw new Error(`Duplicate discriminator value "${String(c)}"`);
        i.set(c, o);
      }
    }
    return i;
  });
  t._zod.parse = (s, i) => {
    const o = s.value;
    if (!Xr(o))
      return s.issues.push({
        code: "invalid_type",
        expected: "object",
        input: o,
        inst: t
      }), s;
    const a = n.value.get(o?.[e.discriminator]);
    return a ? a._zod.run(s, i) : e.unionFallback || i.direction === "backward" ? r(s, i) : (s.issues.push({
      code: "invalid_union",
      errors: [],
      note: "No matching discriminator",
      discriminator: e.discriminator,
      options: Array.from(n.value.keys()),
      input: o,
      path: [e.discriminator],
      inst: t
    }), s);
  };
}), Yp = /* @__PURE__ */ M("$ZodIntersection", (t, e) => {
  Re.init(t, e), t._zod.parse = (r, n) => {
    const s = r.value, i = e.left._zod.run({ value: s, issues: [] }, n), o = e.right._zod.run({ value: s, issues: [] }, n);
    return i instanceof Promise || o instanceof Promise ? Promise.all([i, o]).then(([c, u]) => Oa(r, c, u)) : Oa(r, i, o);
  };
});
function Fi(t, e) {
  if (t === e)
    return { valid: !0, data: t };
  if (t instanceof Date && e instanceof Date && +t == +e)
    return { valid: !0, data: t };
  if (Sr(t) && Sr(e)) {
    const r = Object.keys(e), n = Object.keys(t).filter((i) => r.indexOf(i) !== -1), s = { ...t, ...e };
    for (const i of n) {
      const o = Fi(t[i], e[i]);
      if (!o.valid)
        return {
          valid: !1,
          mergeErrorPath: [i, ...o.mergeErrorPath]
        };
      s[i] = o.data;
    }
    return { valid: !0, data: s };
  }
  if (Array.isArray(t) && Array.isArray(e)) {
    if (t.length !== e.length)
      return { valid: !1, mergeErrorPath: [] };
    const r = [];
    for (let n = 0; n < t.length; n++) {
      const s = t[n], i = e[n], o = Fi(s, i);
      if (!o.valid)
        return {
          valid: !1,
          mergeErrorPath: [n, ...o.mergeErrorPath]
        };
      r.push(o.data);
    }
    return { valid: !0, data: r };
  }
  return { valid: !1, mergeErrorPath: [] };
}
function Oa(t, e, r) {
  const n = /* @__PURE__ */ new Map();
  let s;
  for (const a of e.issues)
    if (a.code === "unrecognized_keys") {
      s ?? (s = a);
      for (const c of a.keys)
        n.has(c) || n.set(c, {}), n.get(c).l = !0;
    } else
      t.issues.push(a);
  for (const a of r.issues)
    if (a.code === "unrecognized_keys")
      for (const c of a.keys)
        n.has(c) || n.set(c, {}), n.get(c).r = !0;
    else
      t.issues.push(a);
  const i = [...n].filter(([, a]) => a.l && a.r).map(([a]) => a);
  if (i.length && s && t.issues.push({ ...s, keys: i }), pr(t))
    return t;
  const o = Fi(e.value, r.value);
  if (!o.valid)
    throw new Error(`Unmergable intersection. Error path: ${JSON.stringify(o.mergeErrorPath)}`);
  return t.value = o.data, t;
}
const Xp = /* @__PURE__ */ M("$ZodRecord", (t, e) => {
  Re.init(t, e), t._zod.parse = (r, n) => {
    const s = r.value;
    if (!Sr(s))
      return r.issues.push({
        expected: "record",
        code: "invalid_type",
        input: s,
        inst: t
      }), r;
    const i = [], o = e.keyType._zod.values;
    if (o) {
      r.value = {};
      const a = /* @__PURE__ */ new Set();
      for (const u of o)
        if (typeof u == "string" || typeof u == "number" || typeof u == "symbol") {
          a.add(typeof u == "number" ? u.toString() : u);
          const l = e.keyType._zod.run({ value: u, issues: [] }, n);
          if (l instanceof Promise)
            throw new Error("Async schemas not supported in object keys currently");
          if (l.issues.length) {
            r.issues.push({
              code: "invalid_key",
              origin: "record",
              issues: l.issues.map((g) => Ct(g, n, Pt())),
              input: u,
              path: [u],
              inst: t
            });
            continue;
          }
          const h = l.value, p = e.valueType._zod.run({ value: s[u], issues: [] }, n);
          p instanceof Promise ? i.push(p.then((g) => {
            g.issues.length && r.issues.push(...mr(u, g.issues)), r.value[h] = g.value;
          })) : (p.issues.length && r.issues.push(...mr(u, p.issues)), r.value[h] = p.value);
        }
      let c;
      for (const u in s)
        a.has(u) || (c = c ?? [], c.push(u));
      c && c.length > 0 && r.issues.push({
        code: "unrecognized_keys",
        input: s,
        inst: t,
        keys: c
      });
    } else {
      r.value = {};
      for (const a of Reflect.ownKeys(s)) {
        if (a === "__proto__" || !Object.prototype.propertyIsEnumerable.call(s, a))
          continue;
        let c = e.keyType._zod.run({ value: a, issues: [] }, n);
        if (c instanceof Promise)
          throw new Error("Async schemas not supported in object keys currently");
        if (typeof a == "string" && Tl.test(a) && c.issues.length) {
          const h = e.keyType._zod.run({ value: Number(a), issues: [] }, n);
          if (h instanceof Promise)
            throw new Error("Async schemas not supported in object keys currently");
          h.issues.length === 0 && (c = h);
        }
        if (c.issues.length) {
          e.mode === "loose" ? r.value[a] = s[a] : r.issues.push({
            code: "invalid_key",
            origin: "record",
            issues: c.issues.map((h) => Ct(h, n, Pt())),
            input: a,
            path: [a],
            inst: t
          });
          continue;
        }
        const l = e.valueType._zod.run({ value: s[a], issues: [] }, n);
        l instanceof Promise ? i.push(l.then((h) => {
          h.issues.length && r.issues.push(...mr(a, h.issues)), r.value[c.value] = h.value;
        })) : (l.issues.length && r.issues.push(...mr(a, l.issues)), r.value[c.value] = l.value);
      }
    }
    return i.length ? Promise.all(i).then(() => r) : r;
  };
}), em = /* @__PURE__ */ M("$ZodEnum", (t, e) => {
  Re.init(t, e);
  const r = yl(e.entries), n = new Set(r);
  t._zod.values = n, t._zod.pattern = new RegExp(`^(${r.filter((s) => cf.has(typeof s)).map((s) => typeof s == "string" ? kr(s) : s.toString()).join("|")})$`), t._zod.parse = (s, i) => {
    const o = s.value;
    return n.has(o) || s.issues.push({
      code: "invalid_value",
      values: r,
      input: o,
      inst: t
    }), s;
  };
}), tm = /* @__PURE__ */ M("$ZodLiteral", (t, e) => {
  if (Re.init(t, e), e.values.length === 0)
    throw new Error("Cannot create literal schema with no valid values");
  const r = new Set(e.values);
  t._zod.values = r, t._zod.pattern = new RegExp(`^(${e.values.map((n) => typeof n == "string" ? kr(n) : n ? kr(n.toString()) : String(n)).join("|")})$`), t._zod.parse = (n, s) => {
    const i = n.value;
    return r.has(i) || n.issues.push({
      code: "invalid_value",
      values: e.values,
      input: i,
      inst: t
    }), n;
  };
}), rm = /* @__PURE__ */ M("$ZodTransform", (t, e) => {
  Re.init(t, e), t._zod.optin = "optional", t._zod.parse = (r, n) => {
    if (n.direction === "backward")
      throw new _l(t.constructor.name);
    const s = e.transform(r.value, r);
    if (n.async)
      return (s instanceof Promise ? s : Promise.resolve(s)).then((o) => (r.value = o, r.fallback = !0, r));
    if (s instanceof Promise)
      throw new br();
    return r.value = s, r.fallback = !0, r;
  };
});
function Aa(t, e) {
  return e === void 0 && (t.issues.length || t.fallback) ? { issues: [], value: void 0 } : t;
}
const jl = /* @__PURE__ */ M("$ZodOptional", (t, e) => {
  Re.init(t, e), t._zod.optin = "optional", t._zod.optout = "optional", Se(t._zod, "values", () => e.innerType._zod.values ? /* @__PURE__ */ new Set([...e.innerType._zod.values, void 0]) : void 0), Se(t._zod, "pattern", () => {
    const r = e.innerType._zod.pattern;
    return r ? new RegExp(`^(${No(r.source)})?$`) : void 0;
  }), t._zod.parse = (r, n) => {
    if (e.innerType._zod.optin === "optional") {
      const s = r.value, i = e.innerType._zod.run(r, n);
      return i instanceof Promise ? i.then((o) => Aa(o, s)) : Aa(i, s);
    }
    return r.value === void 0 ? r : e.innerType._zod.run(r, n);
  };
}), nm = /* @__PURE__ */ M("$ZodExactOptional", (t, e) => {
  jl.init(t, e), Se(t._zod, "values", () => e.innerType._zod.values), Se(t._zod, "pattern", () => e.innerType._zod.pattern), t._zod.parse = (r, n) => e.innerType._zod.run(r, n);
}), sm = /* @__PURE__ */ M("$ZodNullable", (t, e) => {
  Re.init(t, e), Se(t._zod, "optin", () => e.innerType._zod.optin), Se(t._zod, "optout", () => e.innerType._zod.optout), Se(t._zod, "pattern", () => {
    const r = e.innerType._zod.pattern;
    return r ? new RegExp(`^(${No(r.source)}|null)$`) : void 0;
  }), Se(t._zod, "values", () => e.innerType._zod.values ? /* @__PURE__ */ new Set([...e.innerType._zod.values, null]) : void 0), t._zod.parse = (r, n) => r.value === null ? r : e.innerType._zod.run(r, n);
}), im = /* @__PURE__ */ M("$ZodDefault", (t, e) => {
  Re.init(t, e), t._zod.optin = "optional", Se(t._zod, "values", () => e.innerType._zod.values), t._zod.parse = (r, n) => {
    if (n.direction === "backward")
      return e.innerType._zod.run(r, n);
    if (r.value === void 0)
      return r.value = e.defaultValue, r;
    const s = e.innerType._zod.run(r, n);
    return s instanceof Promise ? s.then((i) => Na(i, e)) : Na(s, e);
  };
});
function Na(t, e) {
  return t.value === void 0 && (t.value = e.defaultValue), t;
}
const om = /* @__PURE__ */ M("$ZodPrefault", (t, e) => {
  Re.init(t, e), t._zod.optin = "optional", Se(t._zod, "values", () => e.innerType._zod.values), t._zod.parse = (r, n) => (n.direction === "backward" || r.value === void 0 && (r.value = e.defaultValue), e.innerType._zod.run(r, n));
}), am = /* @__PURE__ */ M("$ZodNonOptional", (t, e) => {
  Re.init(t, e), Se(t._zod, "values", () => {
    const r = e.innerType._zod.values;
    return r ? new Set([...r].filter((n) => n !== void 0)) : void 0;
  }), t._zod.parse = (r, n) => {
    const s = e.innerType._zod.run(r, n);
    return s instanceof Promise ? s.then((i) => xa(i, t)) : xa(s, t);
  };
});
function xa(t, e) {
  return !t.issues.length && t.value === void 0 && t.issues.push({
    code: "invalid_type",
    expected: "nonoptional",
    input: t.value,
    inst: e
  }), t;
}
const cm = /* @__PURE__ */ M("$ZodCatch", (t, e) => {
  Re.init(t, e), t._zod.optin = "optional", Se(t._zod, "optout", () => e.innerType._zod.optout), Se(t._zod, "values", () => e.innerType._zod.values), t._zod.parse = (r, n) => {
    if (n.direction === "backward")
      return e.innerType._zod.run(r, n);
    const s = e.innerType._zod.run(r, n);
    return s instanceof Promise ? s.then((i) => (r.value = i.value, i.issues.length && (r.value = e.catchValue({
      ...r,
      error: {
        issues: i.issues.map((o) => Ct(o, n, Pt()))
      },
      input: r.value
    }), r.issues = [], r.fallback = !0), r)) : (r.value = s.value, s.issues.length && (r.value = e.catchValue({
      ...r,
      error: {
        issues: s.issues.map((i) => Ct(i, n, Pt()))
      },
      input: r.value
    }), r.issues = [], r.fallback = !0), r);
  };
}), Ml = /* @__PURE__ */ M("$ZodPipe", (t, e) => {
  Re.init(t, e), Se(t._zod, "values", () => e.in._zod.values), Se(t._zod, "optin", () => e.in._zod.optin), Se(t._zod, "optout", () => e.out._zod.optout), Se(t._zod, "propValues", () => e.in._zod.propValues), t._zod.parse = (r, n) => {
    if (n.direction === "backward") {
      const i = e.out._zod.run(r, n);
      return i instanceof Promise ? i.then((o) => En(o, e.in, n)) : En(i, e.in, n);
    }
    const s = e.in._zod.run(r, n);
    return s instanceof Promise ? s.then((i) => En(i, e.out, n)) : En(s, e.out, n);
  };
});
function En(t, e, r) {
  return t.issues.length ? (t.aborted = !0, t) : e._zod.run({ value: t.value, issues: t.issues, fallback: t.fallback }, r);
}
const um = /* @__PURE__ */ M("$ZodPreprocess", (t, e) => {
  Ml.init(t, e);
}), lm = /* @__PURE__ */ M("$ZodReadonly", (t, e) => {
  Re.init(t, e), Se(t._zod, "propValues", () => e.innerType._zod.propValues), Se(t._zod, "values", () => e.innerType._zod.values), Se(t._zod, "optin", () => e.innerType?._zod?.optin), Se(t._zod, "optout", () => e.innerType?._zod?.optout), t._zod.parse = (r, n) => {
    if (n.direction === "backward")
      return e.innerType._zod.run(r, n);
    const s = e.innerType._zod.run(r, n);
    return s instanceof Promise ? s.then(za) : za(s);
  };
});
function za(t) {
  return t.value = Object.freeze(t.value), t;
}
const dm = /* @__PURE__ */ M("$ZodCustom", (t, e) => {
  tt.init(t, e), Re.init(t, e), t._zod.parse = (r, n) => r, t._zod.check = (r) => {
    const n = r.value, s = e.fn(n);
    if (s instanceof Promise)
      return s.then((i) => ja(i, r, n, t));
    ja(s, r, n, t);
  };
});
function ja(t, e, r, n) {
  if (!t) {
    const s = {
      code: "custom",
      input: r,
      inst: n,
      // incorporates params.error into issue reporting
      path: [...n._zod.def.path ?? []],
      // incorporates params.error into issue reporting
      continue: !n._zod.def.abort
      // params: inst._zod.def.params,
    };
    n._zod.def.params && (s.params = n._zod.def.params), e.issues.push(en(s));
  }
}
var Ma;
class hm {
  constructor() {
    this._map = /* @__PURE__ */ new WeakMap(), this._idmap = /* @__PURE__ */ new Map();
  }
  add(e, ...r) {
    const n = r[0];
    return this._map.set(e, n), n && typeof n == "object" && "id" in n && this._idmap.set(n.id, e), this;
  }
  clear() {
    return this._map = /* @__PURE__ */ new WeakMap(), this._idmap = /* @__PURE__ */ new Map(), this;
  }
  remove(e) {
    const r = this._map.get(e);
    return r && typeof r == "object" && "id" in r && this._idmap.delete(r.id), this._map.delete(e), this;
  }
  get(e) {
    const r = e._zod.parent;
    if (r) {
      const n = { ...this.get(r) ?? {} };
      delete n.id;
      const s = { ...n, ...this._map.get(e) };
      return Object.keys(s).length ? s : void 0;
    }
    return this._map.get(e);
  }
  has(e) {
    return this._map.has(e);
  }
}
function fm() {
  return new hm();
}
(Ma = globalThis).__zod_globalRegistry ?? (Ma.__zod_globalRegistry = fm());
const Br = globalThis.__zod_globalRegistry;
// @__NO_SIDE_EFFECTS__
function pm(t, e) {
  return new t({
    type: "string",
    ...Q(e)
  });
}
// @__NO_SIDE_EFFECTS__
function mm(t, e) {
  return new t({
    type: "string",
    format: "email",
    check: "string_format",
    abort: !1,
    ...Q(e)
  });
}
// @__NO_SIDE_EFFECTS__
function qa(t, e) {
  return new t({
    type: "string",
    format: "guid",
    check: "string_format",
    abort: !1,
    ...Q(e)
  });
}
// @__NO_SIDE_EFFECTS__
function gm(t, e) {
  return new t({
    type: "string",
    format: "uuid",
    check: "string_format",
    abort: !1,
    ...Q(e)
  });
}
// @__NO_SIDE_EFFECTS__
function _m(t, e) {
  return new t({
    type: "string",
    format: "uuid",
    check: "string_format",
    abort: !1,
    version: "v4",
    ...Q(e)
  });
}
// @__NO_SIDE_EFFECTS__
function ym(t, e) {
  return new t({
    type: "string",
    format: "uuid",
    check: "string_format",
    abort: !1,
    version: "v6",
    ...Q(e)
  });
}
// @__NO_SIDE_EFFECTS__
function wm(t, e) {
  return new t({
    type: "string",
    format: "uuid",
    check: "string_format",
    abort: !1,
    version: "v7",
    ...Q(e)
  });
}
// @__NO_SIDE_EFFECTS__
function ql(t, e) {
  return new t({
    type: "string",
    format: "url",
    check: "string_format",
    abort: !1,
    ...Q(e)
  });
}
// @__NO_SIDE_EFFECTS__
function vm(t, e) {
  return new t({
    type: "string",
    format: "emoji",
    check: "string_format",
    abort: !1,
    ...Q(e)
  });
}
// @__NO_SIDE_EFFECTS__
function bm(t, e) {
  return new t({
    type: "string",
    format: "nanoid",
    check: "string_format",
    abort: !1,
    ...Q(e)
  });
}
// @__NO_SIDE_EFFECTS__
function Sm(t, e) {
  return new t({
    type: "string",
    format: "cuid",
    check: "string_format",
    abort: !1,
    ...Q(e)
  });
}
// @__NO_SIDE_EFFECTS__
function km(t, e) {
  return new t({
    type: "string",
    format: "cuid2",
    check: "string_format",
    abort: !1,
    ...Q(e)
  });
}
// @__NO_SIDE_EFFECTS__
function $m(t, e) {
  return new t({
    type: "string",
    format: "ulid",
    check: "string_format",
    abort: !1,
    ...Q(e)
  });
}
// @__NO_SIDE_EFFECTS__
function Em(t, e) {
  return new t({
    type: "string",
    format: "xid",
    check: "string_format",
    abort: !1,
    ...Q(e)
  });
}
// @__NO_SIDE_EFFECTS__
function Tm(t, e) {
  return new t({
    type: "string",
    format: "ksuid",
    check: "string_format",
    abort: !1,
    ...Q(e)
  });
}
// @__NO_SIDE_EFFECTS__
function Rm(t, e) {
  return new t({
    type: "string",
    format: "ipv4",
    check: "string_format",
    abort: !1,
    ...Q(e)
  });
}
// @__NO_SIDE_EFFECTS__
function Im(t, e) {
  return new t({
    type: "string",
    format: "ipv6",
    check: "string_format",
    abort: !1,
    ...Q(e)
  });
}
// @__NO_SIDE_EFFECTS__
function Pm(t, e) {
  return new t({
    type: "string",
    format: "cidrv4",
    check: "string_format",
    abort: !1,
    ...Q(e)
  });
}
// @__NO_SIDE_EFFECTS__
function Cm(t, e) {
  return new t({
    type: "string",
    format: "cidrv6",
    check: "string_format",
    abort: !1,
    ...Q(e)
  });
}
// @__NO_SIDE_EFFECTS__
function Om(t, e) {
  return new t({
    type: "string",
    format: "base64",
    check: "string_format",
    abort: !1,
    ...Q(e)
  });
}
// @__NO_SIDE_EFFECTS__
function Am(t, e) {
  return new t({
    type: "string",
    format: "base64url",
    check: "string_format",
    abort: !1,
    ...Q(e)
  });
}
// @__NO_SIDE_EFFECTS__
function Nm(t, e) {
  return new t({
    type: "string",
    format: "e164",
    check: "string_format",
    abort: !1,
    ...Q(e)
  });
}
// @__NO_SIDE_EFFECTS__
function xm(t, e) {
  return new t({
    type: "string",
    format: "jwt",
    check: "string_format",
    abort: !1,
    ...Q(e)
  });
}
// @__NO_SIDE_EFFECTS__
function zm(t, e) {
  return new t({
    type: "string",
    format: "datetime",
    check: "string_format",
    offset: !1,
    local: !1,
    precision: null,
    ...Q(e)
  });
}
// @__NO_SIDE_EFFECTS__
function jm(t, e) {
  return new t({
    type: "string",
    format: "date",
    check: "string_format",
    ...Q(e)
  });
}
// @__NO_SIDE_EFFECTS__
function Mm(t, e) {
  return new t({
    type: "string",
    format: "time",
    check: "string_format",
    precision: null,
    ...Q(e)
  });
}
// @__NO_SIDE_EFFECTS__
function qm(t, e) {
  return new t({
    type: "string",
    format: "duration",
    check: "string_format",
    ...Q(e)
  });
}
// @__NO_SIDE_EFFECTS__
function Um(t, e) {
  return new t({
    type: "number",
    checks: [],
    ...Q(e)
  });
}
// @__NO_SIDE_EFFECTS__
function Dm(t, e) {
  return new t({
    type: "number",
    coerce: !0,
    checks: [],
    ...Q(e)
  });
}
// @__NO_SIDE_EFFECTS__
function Lm(t, e) {
  return new t({
    type: "number",
    check: "number_format",
    abort: !1,
    format: "safeint",
    ...Q(e)
  });
}
// @__NO_SIDE_EFFECTS__
function Zm(t, e) {
  return new t({
    type: "boolean",
    ...Q(e)
  });
}
// @__NO_SIDE_EFFECTS__
function Hm(t, e) {
  return new t({
    type: "null",
    ...Q(e)
  });
}
// @__NO_SIDE_EFFECTS__
function Fm(t) {
  return new t({
    type: "any"
  });
}
// @__NO_SIDE_EFFECTS__
function Vm(t) {
  return new t({
    type: "unknown"
  });
}
// @__NO_SIDE_EFFECTS__
function Bm(t, e) {
  return new t({
    type: "never",
    ...Q(e)
  });
}
// @__NO_SIDE_EFFECTS__
function Ua(t, e) {
  return new Il({
    check: "less_than",
    ...Q(e),
    value: t,
    inclusive: !1
  });
}
// @__NO_SIDE_EFFECTS__
function li(t, e) {
  return new Il({
    check: "less_than",
    ...Q(e),
    value: t,
    inclusive: !0
  });
}
// @__NO_SIDE_EFFECTS__
function Da(t, e) {
  return new Pl({
    check: "greater_than",
    ...Q(e),
    value: t,
    inclusive: !1
  });
}
// @__NO_SIDE_EFFECTS__
function di(t, e) {
  return new Pl({
    check: "greater_than",
    ...Q(e),
    value: t,
    inclusive: !0
  });
}
// @__NO_SIDE_EFFECTS__
function La(t, e) {
  return new sp({
    check: "multiple_of",
    ...Q(e),
    value: t
  });
}
// @__NO_SIDE_EFFECTS__
function Ul(t, e) {
  return new op({
    check: "max_length",
    ...Q(e),
    maximum: t
  });
}
// @__NO_SIDE_EFFECTS__
function vs(t, e) {
  return new ap({
    check: "min_length",
    ...Q(e),
    minimum: t
  });
}
// @__NO_SIDE_EFFECTS__
function Dl(t, e) {
  return new cp({
    check: "length_equals",
    ...Q(e),
    length: t
  });
}
// @__NO_SIDE_EFFECTS__
function Wm(t, e) {
  return new up({
    check: "string_format",
    format: "regex",
    ...Q(e),
    pattern: t
  });
}
// @__NO_SIDE_EFFECTS__
function Gm(t) {
  return new lp({
    check: "string_format",
    format: "lowercase",
    ...Q(t)
  });
}
// @__NO_SIDE_EFFECTS__
function Jm(t) {
  return new dp({
    check: "string_format",
    format: "uppercase",
    ...Q(t)
  });
}
// @__NO_SIDE_EFFECTS__
function Km(t, e) {
  return new hp({
    check: "string_format",
    format: "includes",
    ...Q(e),
    includes: t
  });
}
// @__NO_SIDE_EFFECTS__
function Qm(t, e) {
  return new fp({
    check: "string_format",
    format: "starts_with",
    ...Q(e),
    prefix: t
  });
}
// @__NO_SIDE_EFFECTS__
function Ym(t, e) {
  return new pp({
    check: "string_format",
    format: "ends_with",
    ...Q(e),
    suffix: t
  });
}
// @__NO_SIDE_EFFECTS__
function zr(t) {
  return new mp({
    check: "overwrite",
    tx: t
  });
}
// @__NO_SIDE_EFFECTS__
function Xm(t) {
  return /* @__PURE__ */ zr((e) => e.normalize(t));
}
// @__NO_SIDE_EFFECTS__
function eg() {
  return /* @__PURE__ */ zr((t) => t.trim());
}
// @__NO_SIDE_EFFECTS__
function tg() {
  return /* @__PURE__ */ zr((t) => t.toLowerCase());
}
// @__NO_SIDE_EFFECTS__
function rg() {
  return /* @__PURE__ */ zr((t) => t.toUpperCase());
}
// @__NO_SIDE_EFFECTS__
function ng() {
  return /* @__PURE__ */ zr((t) => of(t));
}
// @__NO_SIDE_EFFECTS__
function sg(t, e, r) {
  return new t({
    type: "array",
    element: e,
    // get element() {
    //   return element;
    // },
    ...Q(r)
  });
}
// @__NO_SIDE_EFFECTS__
function ig(t, e, r) {
  const n = Q(r);
  return n.abort ?? (n.abort = !0), new t({
    type: "custom",
    check: "custom",
    fn: e,
    ...n
  });
}
// @__NO_SIDE_EFFECTS__
function og(t, e, r) {
  return new t({
    type: "custom",
    check: "custom",
    fn: e,
    ...Q(r)
  });
}
// @__NO_SIDE_EFFECTS__
function ag(t, e) {
  const r = /* @__PURE__ */ cg((n) => (n.addIssue = (s) => {
    if (typeof s == "string")
      n.issues.push(en(s, n.value, r._zod.def));
    else {
      const i = s;
      i.fatal && (i.continue = !1), i.code ?? (i.code = "custom"), i.input ?? (i.input = n.value), i.inst ?? (i.inst = r), i.continue ?? (i.continue = !r._zod.def.abort), n.issues.push(en(i));
    }
  }, t(n.value, n)), e);
  return r;
}
// @__NO_SIDE_EFFECTS__
function cg(t, e) {
  const r = new tt({
    check: "custom",
    ...Q(e)
  });
  return r._zod.check = t, r;
}
function bs(t) {
  let e = t?.target ?? "draft-2020-12";
  return e === "draft-4" && (e = "draft-04"), e === "draft-7" && (e = "draft-07"), {
    processors: t.processors ?? {},
    metadataRegistry: t?.metadata ?? Br,
    target: e,
    unrepresentable: t?.unrepresentable ?? "throw",
    override: t?.override ?? (() => {
    }),
    io: t?.io ?? "output",
    counter: 0,
    seen: /* @__PURE__ */ new Map(),
    cycles: t?.cycles ?? "ref",
    reused: t?.reused ?? "inline",
    external: t?.external ?? void 0
  };
}
function Te(t, e, r = { path: [], schemaPath: [] }) {
  var n;
  const s = t._zod.def, i = e.seen.get(t);
  if (i)
    return i.count++, r.schemaPath.includes(t) && (i.cycle = r.path), i.schema;
  const o = { schema: {}, count: 1, cycle: void 0, path: r.path };
  e.seen.set(t, o);
  const a = t._zod.toJSONSchema?.();
  if (a)
    o.schema = a;
  else {
    const l = {
      ...r,
      schemaPath: [...r.schemaPath, t],
      path: r.path
    };
    if (t._zod.processJSONSchema)
      t._zod.processJSONSchema(e, o.schema, l);
    else {
      const p = o.schema, g = e.processors[s.type];
      if (!g)
        throw new Error(`[toJSONSchema]: Non-representable type encountered: ${s.type}`);
      g(t, e, p, l);
    }
    const h = t._zod.parent;
    h && (o.ref || (o.ref = h), Te(h, e, l), e.seen.get(h).isParent = !0);
  }
  const c = e.metadataRegistry.get(t);
  return c && Object.assign(o.schema, c), e.io === "input" && Qe(t) && (delete o.schema.examples, delete o.schema.default), e.io === "input" && "_prefault" in o.schema && ((n = o.schema).default ?? (n.default = o.schema._prefault)), delete o.schema._prefault, e.seen.get(t).schema;
}
function Ss(t, e) {
  const r = t.seen.get(e);
  if (!r)
    throw new Error("Unprocessed schema. This is a bug in Zod.");
  const n = /* @__PURE__ */ new Map();
  for (const o of t.seen.entries()) {
    const a = t.metadataRegistry.get(o[0])?.id;
    if (a) {
      const c = n.get(a);
      if (c && c !== o[0])
        throw new Error(`Duplicate schema id "${a}" detected during JSON Schema conversion. Two different schemas cannot share the same id when converted together.`);
      n.set(a, o[0]);
    }
  }
  const s = (o) => {
    const a = t.target === "draft-2020-12" ? "$defs" : "definitions";
    if (t.external) {
      const h = t.external.registry.get(o[0])?.id, p = t.external.uri ?? ((S) => S);
      if (h)
        return { ref: p(h) };
      const g = o[1].defId ?? o[1].schema.id ?? `schema${t.counter++}`;
      return o[1].defId = g, { defId: g, ref: `${p("__shared")}#/${a}/${g}` };
    }
    if (o[1] === r)
      return { ref: "#" };
    const u = `#/${a}/`, l = o[1].schema.id ?? `__schema${t.counter++}`;
    return { defId: l, ref: u + l };
  }, i = (o) => {
    if (o[1].schema.$ref)
      return;
    const a = o[1], { ref: c, defId: u } = s(o);
    a.def = { ...a.schema }, u && (a.defId = u);
    const l = a.schema;
    for (const h in l)
      delete l[h];
    l.$ref = c;
  };
  if (t.cycles === "throw")
    for (const o of t.seen.entries()) {
      const a = o[1];
      if (a.cycle)
        throw new Error(`Cycle detected: #/${a.cycle?.join("/")}/<root>

Set the \`cycles\` parameter to \`"ref"\` to resolve cyclical schemas with defs.`);
    }
  for (const o of t.seen.entries()) {
    const a = o[1];
    if (e === o[0]) {
      i(o);
      continue;
    }
    if (t.external) {
      const u = t.external.registry.get(o[0])?.id;
      if (e !== o[0] && u) {
        i(o);
        continue;
      }
    }
    if (t.metadataRegistry.get(o[0])?.id) {
      i(o);
      continue;
    }
    if (a.cycle) {
      i(o);
      continue;
    }
    if (a.count > 1 && t.reused === "ref") {
      i(o);
      continue;
    }
  }
}
function ks(t, e) {
  const r = t.seen.get(e);
  if (!r)
    throw new Error("Unprocessed schema. This is a bug in Zod.");
  const n = (a) => {
    const c = t.seen.get(a);
    if (c.ref === null)
      return;
    const u = c.def ?? c.schema, l = { ...u }, h = c.ref;
    if (c.ref = null, h) {
      n(h);
      const g = t.seen.get(h), S = g.schema;
      if (S.$ref && (t.target === "draft-07" || t.target === "draft-04" || t.target === "openapi-3.0") ? (u.allOf = u.allOf ?? [], u.allOf.push(S)) : Object.assign(u, S), Object.assign(u, l), a._zod.parent === h)
        for (const y in u)
          y === "$ref" || y === "allOf" || y in l || delete u[y];
      if (S.$ref && g.def)
        for (const y in u)
          y === "$ref" || y === "allOf" || y in g.def && JSON.stringify(u[y]) === JSON.stringify(g.def[y]) && delete u[y];
    }
    const p = a._zod.parent;
    if (p && p !== h) {
      n(p);
      const g = t.seen.get(p);
      if (g?.schema.$ref && (u.$ref = g.schema.$ref, g.def))
        for (const S in u)
          S === "$ref" || S === "allOf" || S in g.def && JSON.stringify(u[S]) === JSON.stringify(g.def[S]) && delete u[S];
    }
    t.override({
      zodSchema: a,
      jsonSchema: u,
      path: c.path ?? []
    });
  };
  for (const a of [...t.seen.entries()].reverse())
    n(a[0]);
  const s = {};
  if (t.target === "draft-2020-12" ? s.$schema = "https://json-schema.org/draft/2020-12/schema" : t.target === "draft-07" ? s.$schema = "http://json-schema.org/draft-07/schema#" : t.target === "draft-04" ? s.$schema = "http://json-schema.org/draft-04/schema#" : t.target, t.external?.uri) {
    const a = t.external.registry.get(e)?.id;
    if (!a)
      throw new Error("Schema is missing an `id` property");
    s.$id = t.external.uri(a);
  }
  Object.assign(s, r.def ?? r.schema);
  const i = t.metadataRegistry.get(e)?.id;
  i !== void 0 && s.id === i && delete s.id;
  const o = t.external?.defs ?? {};
  for (const a of t.seen.entries()) {
    const c = a[1];
    c.def && c.defId && (c.def.id === c.defId && delete c.def.id, o[c.defId] = c.def);
  }
  t.external || Object.keys(o).length > 0 && (t.target === "draft-2020-12" ? s.$defs = o : s.definitions = o);
  try {
    const a = JSON.parse(JSON.stringify(s));
    return Object.defineProperty(a, "~standard", {
      value: {
        ...e["~standard"],
        jsonSchema: {
          input: $s(e, "input", t.processors),
          output: $s(e, "output", t.processors)
        }
      },
      enumerable: !1,
      writable: !1
    }), a;
  } catch {
    throw new Error("Error converting schema to JSON.");
  }
}
function Qe(t, e) {
  const r = e ?? { seen: /* @__PURE__ */ new Set() };
  if (r.seen.has(t))
    return !1;
  r.seen.add(t);
  const n = t._zod.def;
  if (n.type === "transform")
    return !0;
  if (n.type === "array")
    return Qe(n.element, r);
  if (n.type === "set")
    return Qe(n.valueType, r);
  if (n.type === "lazy")
    return Qe(n.getter(), r);
  if (n.type === "promise" || n.type === "optional" || n.type === "nonoptional" || n.type === "nullable" || n.type === "readonly" || n.type === "default" || n.type === "prefault")
    return Qe(n.innerType, r);
  if (n.type === "intersection")
    return Qe(n.left, r) || Qe(n.right, r);
  if (n.type === "record" || n.type === "map")
    return Qe(n.keyType, r) || Qe(n.valueType, r);
  if (n.type === "pipe")
    return t._zod.traits.has("$ZodCodec") ? !0 : Qe(n.in, r) || Qe(n.out, r);
  if (n.type === "object") {
    for (const s in n.shape)
      if (Qe(n.shape[s], r))
        return !0;
    return !1;
  }
  if (n.type === "union") {
    for (const s of n.options)
      if (Qe(s, r))
        return !0;
    return !1;
  }
  if (n.type === "tuple") {
    for (const s of n.items)
      if (Qe(s, r))
        return !0;
    return !!(n.rest && Qe(n.rest, r));
  }
  return !1;
}
const ug = (t, e = {}) => (r) => {
  const n = bs({ ...r, processors: e });
  return Te(t, n), Ss(n, t), ks(n, t);
}, $s = (t, e, r = {}) => (n) => {
  const { libraryOptions: s, target: i } = n ?? {}, o = bs({ ...s ?? {}, target: i, io: e, processors: r });
  return Te(t, o), Ss(o, t), ks(o, t);
}, lg = {
  guid: "uuid",
  url: "uri",
  datetime: "date-time",
  json_string: "json-string",
  regex: ""
  // do not set
}, Ll = (t, e, r, n) => {
  const s = r;
  s.type = "string";
  const { minimum: i, maximum: o, format: a, patterns: c, contentEncoding: u } = t._zod.bag;
  if (typeof i == "number" && (s.minLength = i), typeof o == "number" && (s.maxLength = o), a && (s.format = lg[a] ?? a, s.format === "" && delete s.format, a === "time" && delete s.format), u && (s.contentEncoding = u), c && c.size > 0) {
    const l = [...c];
    l.length === 1 ? s.pattern = l[0].source : l.length > 1 && (s.allOf = [
      ...l.map((h) => ({
        ...e.target === "draft-07" || e.target === "draft-04" || e.target === "openapi-3.0" ? { type: "string" } : {},
        pattern: h.source
      }))
    ]);
  }
}, Zl = (t, e, r, n) => {
  const s = r, { minimum: i, maximum: o, format: a, multipleOf: c, exclusiveMaximum: u, exclusiveMinimum: l } = t._zod.bag;
  typeof a == "string" && a.includes("int") ? s.type = "integer" : s.type = "number";
  const h = typeof l == "number" && l >= (i ?? Number.NEGATIVE_INFINITY), p = typeof u == "number" && u <= (o ?? Number.POSITIVE_INFINITY), g = e.target === "draft-04" || e.target === "openapi-3.0";
  h ? g ? (s.minimum = l, s.exclusiveMinimum = !0) : s.exclusiveMinimum = l : typeof i == "number" && (s.minimum = i), p ? g ? (s.maximum = u, s.exclusiveMaximum = !0) : s.exclusiveMaximum = u : typeof o == "number" && (s.maximum = o), typeof c == "number" && (s.multipleOf = c);
}, Hl = (t, e, r, n) => {
  r.type = "boolean";
}, dg = (t, e, r, n) => {
  if (e.unrepresentable === "throw")
    throw new Error("BigInt cannot be represented in JSON Schema");
}, hg = (t, e, r, n) => {
  if (e.unrepresentable === "throw")
    throw new Error("Symbols cannot be represented in JSON Schema");
}, Fl = (t, e, r, n) => {
  e.target === "openapi-3.0" ? (r.type = "string", r.nullable = !0, r.enum = [null]) : r.type = "null";
}, fg = (t, e, r, n) => {
  if (e.unrepresentable === "throw")
    throw new Error("Undefined cannot be represented in JSON Schema");
}, pg = (t, e, r, n) => {
  if (e.unrepresentable === "throw")
    throw new Error("Void cannot be represented in JSON Schema");
}, Vl = (t, e, r, n) => {
  r.not = {};
}, Bl = (t, e, r, n) => {
}, Wl = (t, e, r, n) => {
}, mg = (t, e, r, n) => {
  if (e.unrepresentable === "throw")
    throw new Error("Date cannot be represented in JSON Schema");
}, Gl = (t, e, r, n) => {
  const s = t._zod.def, i = yl(s.entries);
  i.every((o) => typeof o == "number") && (r.type = "number"), i.every((o) => typeof o == "string") && (r.type = "string"), r.enum = i;
}, Jl = (t, e, r, n) => {
  const s = t._zod.def, i = [];
  for (const o of s.values)
    if (o === void 0) {
      if (e.unrepresentable === "throw")
        throw new Error("Literal `undefined` cannot be represented in JSON Schema");
    } else if (typeof o == "bigint") {
      if (e.unrepresentable === "throw")
        throw new Error("BigInt literals cannot be represented in JSON Schema");
      i.push(Number(o));
    } else
      i.push(o);
  if (i.length !== 0) if (i.length === 1) {
    const o = i[0];
    r.type = o === null ? "null" : typeof o, e.target === "draft-04" || e.target === "openapi-3.0" ? r.enum = [o] : r.const = o;
  } else
    i.every((o) => typeof o == "number") && (r.type = "number"), i.every((o) => typeof o == "string") && (r.type = "string"), i.every((o) => typeof o == "boolean") && (r.type = "boolean"), i.every((o) => o === null) && (r.type = "null"), r.enum = i;
}, gg = (t, e, r, n) => {
  if (e.unrepresentable === "throw")
    throw new Error("NaN cannot be represented in JSON Schema");
}, _g = (t, e, r, n) => {
  const s = r, i = t._zod.pattern;
  if (!i)
    throw new Error("Pattern not found in template literal");
  s.type = "string", s.pattern = i.source;
}, yg = (t, e, r, n) => {
  const s = r, i = {
    type: "string",
    format: "binary",
    contentEncoding: "binary"
  }, { minimum: o, maximum: a, mime: c } = t._zod.bag;
  o !== void 0 && (i.minLength = o), a !== void 0 && (i.maxLength = a), c ? c.length === 1 ? (i.contentMediaType = c[0], Object.assign(s, i)) : (Object.assign(s, i), s.anyOf = c.map((u) => ({ contentMediaType: u }))) : Object.assign(s, i);
}, wg = (t, e, r, n) => {
  r.type = "boolean";
}, Kl = (t, e, r, n) => {
  if (e.unrepresentable === "throw")
    throw new Error("Custom types cannot be represented in JSON Schema");
}, vg = (t, e, r, n) => {
  if (e.unrepresentable === "throw")
    throw new Error("Function types cannot be represented in JSON Schema");
}, Ql = (t, e, r, n) => {
  if (e.unrepresentable === "throw")
    throw new Error("Transforms cannot be represented in JSON Schema");
}, bg = (t, e, r, n) => {
  if (e.unrepresentable === "throw")
    throw new Error("Map cannot be represented in JSON Schema");
}, Sg = (t, e, r, n) => {
  if (e.unrepresentable === "throw")
    throw new Error("Set cannot be represented in JSON Schema");
}, Yl = (t, e, r, n) => {
  const s = r, i = t._zod.def, { minimum: o, maximum: a } = t._zod.bag;
  typeof o == "number" && (s.minItems = o), typeof a == "number" && (s.maxItems = a), s.type = "array", s.items = Te(i.element, e, {
    ...n,
    path: [...n.path, "items"]
  });
}, Xl = (t, e, r, n) => {
  const s = r, i = t._zod.def;
  s.type = "object", s.properties = {};
  const o = i.shape;
  for (const u in o)
    s.properties[u] = Te(o[u], e, {
      ...n,
      path: [...n.path, "properties", u]
    });
  const a = new Set(Object.keys(o)), c = new Set([...a].filter((u) => {
    const l = i.shape[u]._zod;
    return e.io === "input" ? l.optin === void 0 : l.optout === void 0;
  }));
  c.size > 0 && (s.required = Array.from(c)), i.catchall?._zod.def.type === "never" ? s.additionalProperties = !1 : i.catchall ? i.catchall && (s.additionalProperties = Te(i.catchall, e, {
    ...n,
    path: [...n.path, "additionalProperties"]
  })) : e.io === "output" && (s.additionalProperties = !1);
}, ed = (t, e, r, n) => {
  const s = t._zod.def, i = s.inclusive === !1, o = s.options.map((a, c) => Te(a, e, {
    ...n,
    path: [...n.path, i ? "oneOf" : "anyOf", c]
  }));
  i ? r.oneOf = o : r.anyOf = o;
}, td = (t, e, r, n) => {
  const s = t._zod.def, i = Te(s.left, e, {
    ...n,
    path: [...n.path, "allOf", 0]
  }), o = Te(s.right, e, {
    ...n,
    path: [...n.path, "allOf", 1]
  }), a = (u) => "allOf" in u && Object.keys(u).length === 1, c = [
    ...a(i) ? i.allOf : [i],
    ...a(o) ? o.allOf : [o]
  ];
  r.allOf = c;
}, kg = (t, e, r, n) => {
  const s = r, i = t._zod.def;
  s.type = "array";
  const o = e.target === "draft-2020-12" ? "prefixItems" : "items", a = e.target === "draft-2020-12" || e.target === "openapi-3.0" ? "items" : "additionalItems", c = i.items.map((p, g) => Te(p, e, {
    ...n,
    path: [...n.path, o, g]
  })), u = i.rest ? Te(i.rest, e, {
    ...n,
    path: [...n.path, a, ...e.target === "openapi-3.0" ? [i.items.length] : []]
  }) : null;
  e.target === "draft-2020-12" ? (s.prefixItems = c, u && (s.items = u)) : e.target === "openapi-3.0" ? (s.items = {
    anyOf: c
  }, u && s.items.anyOf.push(u), s.minItems = c.length, u || (s.maxItems = c.length)) : (s.items = c, u && (s.additionalItems = u));
  const { minimum: l, maximum: h } = t._zod.bag;
  typeof l == "number" && (s.minItems = l), typeof h == "number" && (s.maxItems = h);
}, rd = (t, e, r, n) => {
  const s = r, i = t._zod.def;
  s.type = "object";
  const o = i.keyType, c = o._zod.bag?.patterns;
  if (i.mode === "loose" && c && c.size > 0) {
    const l = Te(i.valueType, e, {
      ...n,
      path: [...n.path, "patternProperties", "*"]
    });
    s.patternProperties = {};
    for (const h of c)
      s.patternProperties[h.source] = l;
  } else
    (e.target === "draft-07" || e.target === "draft-2020-12") && (s.propertyNames = Te(i.keyType, e, {
      ...n,
      path: [...n.path, "propertyNames"]
    })), s.additionalProperties = Te(i.valueType, e, {
      ...n,
      path: [...n.path, "additionalProperties"]
    });
  const u = o._zod.values;
  if (u) {
    const l = [...u].filter((h) => typeof h == "string" || typeof h == "number");
    l.length > 0 && (s.required = l);
  }
}, nd = (t, e, r, n) => {
  const s = t._zod.def, i = Te(s.innerType, e, n), o = e.seen.get(t);
  e.target === "openapi-3.0" ? (o.ref = s.innerType, r.nullable = !0) : r.anyOf = [i, { type: "null" }];
}, sd = (t, e, r, n) => {
  const s = t._zod.def;
  Te(s.innerType, e, n);
  const i = e.seen.get(t);
  i.ref = s.innerType;
}, id = (t, e, r, n) => {
  const s = t._zod.def;
  Te(s.innerType, e, n);
  const i = e.seen.get(t);
  i.ref = s.innerType, r.default = JSON.parse(JSON.stringify(s.defaultValue));
}, od = (t, e, r, n) => {
  const s = t._zod.def;
  Te(s.innerType, e, n);
  const i = e.seen.get(t);
  i.ref = s.innerType, e.io === "input" && (r._prefault = JSON.parse(JSON.stringify(s.defaultValue)));
}, ad = (t, e, r, n) => {
  const s = t._zod.def;
  Te(s.innerType, e, n);
  const i = e.seen.get(t);
  i.ref = s.innerType;
  let o;
  try {
    o = s.catchValue(void 0);
  } catch {
    throw new Error("Dynamic catch values are not supported in JSON Schema");
  }
  r.default = o;
}, cd = (t, e, r, n) => {
  const s = t._zod.def, i = s.in._zod.traits.has("$ZodTransform"), o = e.io === "input" ? i ? s.out : s.in : s.out;
  Te(o, e, n);
  const a = e.seen.get(t);
  a.ref = o;
}, ud = (t, e, r, n) => {
  const s = t._zod.def;
  Te(s.innerType, e, n);
  const i = e.seen.get(t);
  i.ref = s.innerType, r.readOnly = !0;
}, $g = (t, e, r, n) => {
  const s = t._zod.def;
  Te(s.innerType, e, n);
  const i = e.seen.get(t);
  i.ref = s.innerType;
}, qo = (t, e, r, n) => {
  const s = t._zod.def;
  Te(s.innerType, e, n);
  const i = e.seen.get(t);
  i.ref = s.innerType;
}, Eg = (t, e, r, n) => {
  const s = t._zod.innerType;
  Te(s, e, n);
  const i = e.seen.get(t);
  i.ref = s;
}, Za = {
  string: Ll,
  number: Zl,
  boolean: Hl,
  bigint: dg,
  symbol: hg,
  null: Fl,
  undefined: fg,
  void: pg,
  never: Vl,
  any: Bl,
  unknown: Wl,
  date: mg,
  enum: Gl,
  literal: Jl,
  nan: gg,
  template_literal: _g,
  file: yg,
  success: wg,
  custom: Kl,
  function: vg,
  transform: Ql,
  map: bg,
  set: Sg,
  array: Yl,
  object: Xl,
  union: ed,
  intersection: td,
  tuple: kg,
  record: rd,
  nullable: nd,
  nonoptional: sd,
  default: id,
  prefault: od,
  catch: ad,
  pipe: cd,
  readonly: ud,
  promise: $g,
  optional: qo,
  lazy: Eg
};
function Tg(t, e) {
  if ("_idmap" in t) {
    const n = t, s = bs({ ...e, processors: Za }), i = {};
    for (const c of n._idmap.entries()) {
      const [u, l] = c;
      Te(l, s);
    }
    const o = {}, a = {
      registry: n,
      uri: e?.uri,
      defs: i
    };
    s.external = a;
    for (const c of n._idmap.entries()) {
      const [u, l] = c;
      Ss(s, l), o[u] = ks(s, l);
    }
    if (Object.keys(i).length > 0) {
      const c = s.target === "draft-2020-12" ? "$defs" : "definitions";
      o.__shared = {
        [c]: i
      };
    }
    return { schemas: o };
  }
  const r = bs({ ...e, processors: Za });
  return Te(t, r), Ss(r, t), ks(r, t);
}
const Rg = /* @__PURE__ */ M("ZodISODateTime", (t, e) => {
  Pp.init(t, e), je.init(t, e);
});
function ld(t) {
  return /* @__PURE__ */ zm(Rg, t);
}
const Ig = /* @__PURE__ */ M("ZodISODate", (t, e) => {
  Cp.init(t, e), je.init(t, e);
});
function Pg(t) {
  return /* @__PURE__ */ jm(Ig, t);
}
const Cg = /* @__PURE__ */ M("ZodISOTime", (t, e) => {
  Op.init(t, e), je.init(t, e);
});
function Og(t) {
  return /* @__PURE__ */ Mm(Cg, t);
}
const Ag = /* @__PURE__ */ M("ZodISODuration", (t, e) => {
  Ap.init(t, e), je.init(t, e);
});
function Ng(t) {
  return /* @__PURE__ */ qm(Ag, t);
}
const xg = (t, e) => {
  Sl.init(t, e), t.name = "ZodError", Object.defineProperties(t, {
    format: {
      value: (r) => vf(t, r)
      // enumerable: false,
    },
    flatten: {
      value: (r) => wf(t, r)
      // enumerable: false,
    },
    addIssue: {
      value: (r) => {
        t.issues.push(r), t.message = JSON.stringify(t.issues, Hi, 2);
      }
      // enumerable: false,
    },
    addIssues: {
      value: (r) => {
        t.issues.push(...r), t.message = JSON.stringify(t.issues, Hi, 2);
      }
      // enumerable: false,
    },
    isEmpty: {
      get() {
        return t.issues.length === 0;
      }
      // enumerable: false,
    }
  });
}, ct = /* @__PURE__ */ M("ZodError", xg, {
  Parent: Error
}), zg = /* @__PURE__ */ Bs(ct), jg = /* @__PURE__ */ Ws(ct), Mg = /* @__PURE__ */ Gs(ct), qg = /* @__PURE__ */ Js(ct), Ug = /* @__PURE__ */ kf(ct), Dg = /* @__PURE__ */ $f(ct), Lg = /* @__PURE__ */ Ef(ct), Zg = /* @__PURE__ */ Tf(ct), Hg = /* @__PURE__ */ Rf(ct), Fg = /* @__PURE__ */ If(ct), Vg = /* @__PURE__ */ Pf(ct), Bg = /* @__PURE__ */ Cf(ct), Ha = /* @__PURE__ */ new WeakMap();
function pn(t, e, r) {
  const n = Object.getPrototypeOf(t);
  let s = Ha.get(n);
  if (s || (s = /* @__PURE__ */ new Set(), Ha.set(n, s)), !s.has(e)) {
    s.add(e);
    for (const i in r) {
      const o = r[i];
      Object.defineProperty(n, i, {
        configurable: !0,
        enumerable: !1,
        get() {
          const a = o.bind(this);
          return Object.defineProperty(this, i, {
            configurable: !0,
            writable: !0,
            enumerable: !0,
            value: a
          }), a;
        },
        set(a) {
          Object.defineProperty(this, i, {
            configurable: !0,
            writable: !0,
            enumerable: !0,
            value: a
          });
        }
      });
    }
  }
}
const Oe = /* @__PURE__ */ M("ZodType", (t, e) => (Re.init(t, e), Object.assign(t["~standard"], {
  jsonSchema: {
    input: $s(t, "input"),
    output: $s(t, "output")
  }
}), t.toJSONSchema = ug(t, {}), t.def = e, t.type = e.type, Object.defineProperty(t, "_def", { value: e }), t.parse = (r, n) => zg(t, r, n, { callee: t.parse }), t.safeParse = (r, n) => Mg(t, r, n), t.parseAsync = async (r, n) => jg(t, r, n, { callee: t.parseAsync }), t.safeParseAsync = async (r, n) => qg(t, r, n), t.spa = t.safeParseAsync, t.encode = (r, n) => Ug(t, r, n), t.decode = (r, n) => Dg(t, r, n), t.encodeAsync = async (r, n) => Lg(t, r, n), t.decodeAsync = async (r, n) => Zg(t, r, n), t.safeEncode = (r, n) => Hg(t, r, n), t.safeDecode = (r, n) => Fg(t, r, n), t.safeEncodeAsync = async (r, n) => Vg(t, r, n), t.safeDecodeAsync = async (r, n) => Bg(t, r, n), pn(t, "ZodType", {
  check(...r) {
    const n = this.def;
    return this.clone(Ft(n, {
      checks: [
        ...n.checks ?? [],
        ...r.map((s) => typeof s == "function" ? { _zod: { check: s, def: { check: "custom" }, onattach: [] } } : s)
      ]
    }), { parent: !0 });
  },
  with(...r) {
    return this.check(...r);
  },
  clone(r, n) {
    return xt(this, r, n);
  },
  brand() {
    return this;
  },
  register(r, n) {
    return r.add(this, n), this;
  },
  refine(r, n) {
    return this.check(U_(r, n));
  },
  superRefine(r, n) {
    return this.check(D_(r, n));
  },
  overwrite(r) {
    return this.check(/* @__PURE__ */ zr(r));
  },
  optional() {
    return xe(this);
  },
  exactOptional() {
    return T_(this);
  },
  nullable() {
    return Wa(this);
  },
  nullish() {
    return xe(Wa(this));
  },
  nonoptional(r) {
    return A_(this, r);
  },
  array() {
    return B(this);
  },
  or(r) {
    return Ne([this, r]);
  },
  and(r) {
    return Do(this, r);
  },
  transform(r) {
    return Ga(this, gd(r));
  },
  default(r) {
    return P_(this, r);
  },
  prefault(r) {
    return O_(this, r);
  },
  catch(r) {
    return x_(this, r);
  },
  pipe(r) {
    return Ga(this, r);
  },
  readonly() {
    return M_(this);
  },
  describe(r) {
    const n = this.clone();
    return Br.add(n, { description: r }), n;
  },
  meta(...r) {
    if (r.length === 0)
      return Br.get(this);
    const n = this.clone();
    return Br.add(n, r[0]), n;
  },
  isOptional() {
    return this.safeParse(void 0).success;
  },
  isNullable() {
    return this.safeParse(null).success;
  },
  apply(r) {
    return r(this);
  }
}), Object.defineProperty(t, "description", {
  get() {
    return Br.get(t)?.description;
  },
  configurable: !0
}), t)), dd = /* @__PURE__ */ M("_ZodString", (t, e) => {
  Mo.init(t, e), Oe.init(t, e), t._zod.processJSONSchema = (n, s, i) => Ll(t, n, s);
  const r = t._zod.bag;
  t.format = r.format ?? null, t.minLength = r.minimum ?? null, t.maxLength = r.maximum ?? null, pn(t, "_ZodString", {
    regex(...n) {
      return this.check(/* @__PURE__ */ Wm(...n));
    },
    includes(...n) {
      return this.check(/* @__PURE__ */ Km(...n));
    },
    startsWith(...n) {
      return this.check(/* @__PURE__ */ Qm(...n));
    },
    endsWith(...n) {
      return this.check(/* @__PURE__ */ Ym(...n));
    },
    min(...n) {
      return this.check(/* @__PURE__ */ vs(...n));
    },
    max(...n) {
      return this.check(/* @__PURE__ */ Ul(...n));
    },
    length(...n) {
      return this.check(/* @__PURE__ */ Dl(...n));
    },
    nonempty(...n) {
      return this.check(/* @__PURE__ */ vs(1, ...n));
    },
    lowercase(n) {
      return this.check(/* @__PURE__ */ Gm(n));
    },
    uppercase(n) {
      return this.check(/* @__PURE__ */ Jm(n));
    },
    trim() {
      return this.check(/* @__PURE__ */ eg());
    },
    normalize(...n) {
      return this.check(/* @__PURE__ */ Xm(...n));
    },
    toLowerCase() {
      return this.check(/* @__PURE__ */ tg());
    },
    toUpperCase() {
      return this.check(/* @__PURE__ */ rg());
    },
    slugify() {
      return this.check(/* @__PURE__ */ ng());
    }
  });
}), Wg = /* @__PURE__ */ M("ZodString", (t, e) => {
  Mo.init(t, e), dd.init(t, e), t.email = (r) => t.check(/* @__PURE__ */ mm(Gg, r)), t.url = (r) => t.check(/* @__PURE__ */ ql(hd, r)), t.jwt = (r) => t.check(/* @__PURE__ */ xm(l_, r)), t.emoji = (r) => t.check(/* @__PURE__ */ vm(Kg, r)), t.guid = (r) => t.check(/* @__PURE__ */ qa(Fa, r)), t.uuid = (r) => t.check(/* @__PURE__ */ gm(Tn, r)), t.uuidv4 = (r) => t.check(/* @__PURE__ */ _m(Tn, r)), t.uuidv6 = (r) => t.check(/* @__PURE__ */ ym(Tn, r)), t.uuidv7 = (r) => t.check(/* @__PURE__ */ wm(Tn, r)), t.nanoid = (r) => t.check(/* @__PURE__ */ bm(Qg, r)), t.guid = (r) => t.check(/* @__PURE__ */ qa(Fa, r)), t.cuid = (r) => t.check(/* @__PURE__ */ Sm(Yg, r)), t.cuid2 = (r) => t.check(/* @__PURE__ */ km(Xg, r)), t.ulid = (r) => t.check(/* @__PURE__ */ $m(e_, r)), t.base64 = (r) => t.check(/* @__PURE__ */ Om(a_, r)), t.base64url = (r) => t.check(/* @__PURE__ */ Am(c_, r)), t.xid = (r) => t.check(/* @__PURE__ */ Em(t_, r)), t.ksuid = (r) => t.check(/* @__PURE__ */ Tm(r_, r)), t.ipv4 = (r) => t.check(/* @__PURE__ */ Rm(n_, r)), t.ipv6 = (r) => t.check(/* @__PURE__ */ Im(s_, r)), t.cidrv4 = (r) => t.check(/* @__PURE__ */ Pm(i_, r)), t.cidrv6 = (r) => t.check(/* @__PURE__ */ Cm(o_, r)), t.e164 = (r) => t.check(/* @__PURE__ */ Nm(u_, r)), t.datetime = (r) => t.check(ld(r)), t.date = (r) => t.check(Pg(r)), t.time = (r) => t.check(Og(r)), t.duration = (r) => t.check(Ng(r));
});
function T(t) {
  return /* @__PURE__ */ pm(Wg, t);
}
const je = /* @__PURE__ */ M("ZodStringFormat", (t, e) => {
  Ae.init(t, e), dd.init(t, e);
}), Gg = /* @__PURE__ */ M("ZodEmail", (t, e) => {
  vp.init(t, e), je.init(t, e);
}), Fa = /* @__PURE__ */ M("ZodGUID", (t, e) => {
  yp.init(t, e), je.init(t, e);
}), Tn = /* @__PURE__ */ M("ZodUUID", (t, e) => {
  wp.init(t, e), je.init(t, e);
}), hd = /* @__PURE__ */ M("ZodURL", (t, e) => {
  bp.init(t, e), je.init(t, e);
});
function Jg(t) {
  return /* @__PURE__ */ ql(hd, t);
}
const Kg = /* @__PURE__ */ M("ZodEmoji", (t, e) => {
  Sp.init(t, e), je.init(t, e);
}), Qg = /* @__PURE__ */ M("ZodNanoID", (t, e) => {
  kp.init(t, e), je.init(t, e);
}), Yg = /* @__PURE__ */ M("ZodCUID", (t, e) => {
  $p.init(t, e), je.init(t, e);
}), Xg = /* @__PURE__ */ M("ZodCUID2", (t, e) => {
  Ep.init(t, e), je.init(t, e);
}), e_ = /* @__PURE__ */ M("ZodULID", (t, e) => {
  Tp.init(t, e), je.init(t, e);
}), t_ = /* @__PURE__ */ M("ZodXID", (t, e) => {
  Rp.init(t, e), je.init(t, e);
}), r_ = /* @__PURE__ */ M("ZodKSUID", (t, e) => {
  Ip.init(t, e), je.init(t, e);
}), n_ = /* @__PURE__ */ M("ZodIPv4", (t, e) => {
  Np.init(t, e), je.init(t, e);
}), s_ = /* @__PURE__ */ M("ZodIPv6", (t, e) => {
  xp.init(t, e), je.init(t, e);
}), i_ = /* @__PURE__ */ M("ZodCIDRv4", (t, e) => {
  zp.init(t, e), je.init(t, e);
}), o_ = /* @__PURE__ */ M("ZodCIDRv6", (t, e) => {
  jp.init(t, e), je.init(t, e);
}), a_ = /* @__PURE__ */ M("ZodBase64", (t, e) => {
  Mp.init(t, e), je.init(t, e);
}), c_ = /* @__PURE__ */ M("ZodBase64URL", (t, e) => {
  Up.init(t, e), je.init(t, e);
}), u_ = /* @__PURE__ */ M("ZodE164", (t, e) => {
  Dp.init(t, e), je.init(t, e);
}), l_ = /* @__PURE__ */ M("ZodJWT", (t, e) => {
  Zp.init(t, e), je.init(t, e);
}), Uo = /* @__PURE__ */ M("ZodNumber", (t, e) => {
  Ol.init(t, e), Oe.init(t, e), t._zod.processJSONSchema = (n, s, i) => Zl(t, n, s), pn(t, "ZodNumber", {
    gt(n, s) {
      return this.check(/* @__PURE__ */ Da(n, s));
    },
    gte(n, s) {
      return this.check(/* @__PURE__ */ di(n, s));
    },
    min(n, s) {
      return this.check(/* @__PURE__ */ di(n, s));
    },
    lt(n, s) {
      return this.check(/* @__PURE__ */ Ua(n, s));
    },
    lte(n, s) {
      return this.check(/* @__PURE__ */ li(n, s));
    },
    max(n, s) {
      return this.check(/* @__PURE__ */ li(n, s));
    },
    int(n) {
      return this.check(Va(n));
    },
    safe(n) {
      return this.check(Va(n));
    },
    positive(n) {
      return this.check(/* @__PURE__ */ Da(0, n));
    },
    nonnegative(n) {
      return this.check(/* @__PURE__ */ di(0, n));
    },
    negative(n) {
      return this.check(/* @__PURE__ */ Ua(0, n));
    },
    nonpositive(n) {
      return this.check(/* @__PURE__ */ li(0, n));
    },
    multipleOf(n, s) {
      return this.check(/* @__PURE__ */ La(n, s));
    },
    step(n, s) {
      return this.check(/* @__PURE__ */ La(n, s));
    },
    finite() {
      return this;
    }
  });
  const r = t._zod.bag;
  t.minValue = Math.max(r.minimum ?? Number.NEGATIVE_INFINITY, r.exclusiveMinimum ?? Number.NEGATIVE_INFINITY) ?? null, t.maxValue = Math.min(r.maximum ?? Number.POSITIVE_INFINITY, r.exclusiveMaximum ?? Number.POSITIVE_INFINITY) ?? null, t.isInt = (r.format ?? "").includes("int") || Number.isSafeInteger(r.multipleOf ?? 0.5), t.isFinite = !0, t.format = r.format ?? null;
});
function ve(t) {
  return /* @__PURE__ */ Um(Uo, t);
}
const d_ = /* @__PURE__ */ M("ZodNumberFormat", (t, e) => {
  Hp.init(t, e), Uo.init(t, e);
});
function Va(t) {
  return /* @__PURE__ */ Lm(d_, t);
}
const h_ = /* @__PURE__ */ M("ZodBoolean", (t, e) => {
  Fp.init(t, e), Oe.init(t, e), t._zod.processJSONSchema = (r, n, s) => Hl(t, r, n);
});
function Pe(t) {
  return /* @__PURE__ */ Zm(h_, t);
}
const f_ = /* @__PURE__ */ M("ZodNull", (t, e) => {
  Vp.init(t, e), Oe.init(t, e), t._zod.processJSONSchema = (r, n, s) => Fl(t, r, n);
});
function p_(t) {
  return /* @__PURE__ */ Hm(f_, t);
}
const m_ = /* @__PURE__ */ M("ZodAny", (t, e) => {
  Bp.init(t, e), Oe.init(t, e), t._zod.processJSONSchema = (r, n, s) => Bl();
});
function g_() {
  return /* @__PURE__ */ Fm(m_);
}
const __ = /* @__PURE__ */ M("ZodUnknown", (t, e) => {
  Wp.init(t, e), Oe.init(t, e), t._zod.processJSONSchema = (r, n, s) => Wl();
});
function ze() {
  return /* @__PURE__ */ Vm(__);
}
const y_ = /* @__PURE__ */ M("ZodNever", (t, e) => {
  Gp.init(t, e), Oe.init(t, e), t._zod.processJSONSchema = (r, n, s) => Vl(t, r, n);
});
function w_(t) {
  return /* @__PURE__ */ Bm(y_, t);
}
const v_ = /* @__PURE__ */ M("ZodArray", (t, e) => {
  Jp.init(t, e), Oe.init(t, e), t._zod.processJSONSchema = (r, n, s) => Yl(t, r, n, s), t.element = e.element, pn(t, "ZodArray", {
    min(r, n) {
      return this.check(/* @__PURE__ */ vs(r, n));
    },
    nonempty(r) {
      return this.check(/* @__PURE__ */ vs(1, r));
    },
    max(r, n) {
      return this.check(/* @__PURE__ */ Ul(r, n));
    },
    length(r, n) {
      return this.check(/* @__PURE__ */ Dl(r, n));
    },
    unwrap() {
      return this.element;
    }
  });
});
function B(t, e) {
  return /* @__PURE__ */ sg(v_, t, e);
}
const fd = /* @__PURE__ */ M("ZodObject", (t, e) => {
  Kp.init(t, e), Oe.init(t, e), t._zod.processJSONSchema = (r, n, s) => Xl(t, r, n, s), Se(t, "shape", () => e.shape), pn(t, "ZodObject", {
    keyof() {
      return st(Object.keys(this._zod.def.shape));
    },
    catchall(r) {
      return this.clone({ ...this._zod.def, catchall: r });
    },
    passthrough() {
      return this.clone({ ...this._zod.def, catchall: ze() });
    },
    loose() {
      return this.clone({ ...this._zod.def, catchall: ze() });
    },
    strict() {
      return this.clone({ ...this._zod.def, catchall: w_() });
    },
    strip() {
      return this.clone({ ...this._zod.def, catchall: void 0 });
    },
    extend(r) {
      return ff(this, r);
    },
    safeExtend(r) {
      return pf(this, r);
    },
    merge(r) {
      return mf(this, r);
    },
    pick(r) {
      return df(this, r);
    },
    omit(r) {
      return hf(this, r);
    },
    partial(...r) {
      return gf(Lo, this, r[0]);
    },
    required(...r) {
      return _f(_d, this, r[0]);
    }
  });
});
function W(t, e) {
  const r = {
    type: "object",
    shape: t ?? {},
    ...Q(e)
  };
  return new fd(r);
}
function Be(t, e) {
  return new fd({
    type: "object",
    shape: t,
    catchall: ze(),
    ...Q(e)
  });
}
const pd = /* @__PURE__ */ M("ZodUnion", (t, e) => {
  zl.init(t, e), Oe.init(t, e), t._zod.processJSONSchema = (r, n, s) => ed(t, r, n, s), t.options = e.options;
});
function Ne(t, e) {
  return new pd({
    type: "union",
    options: t,
    ...Q(e)
  });
}
const b_ = /* @__PURE__ */ M("ZodDiscriminatedUnion", (t, e) => {
  pd.init(t, e), Qp.init(t, e);
});
function md(t, e, r) {
  return new b_({
    type: "union",
    options: e,
    discriminator: t,
    ...Q(r)
  });
}
const S_ = /* @__PURE__ */ M("ZodIntersection", (t, e) => {
  Yp.init(t, e), Oe.init(t, e), t._zod.processJSONSchema = (r, n, s) => td(t, r, n, s);
});
function Do(t, e) {
  return new S_({
    type: "intersection",
    left: t,
    right: e
  });
}
const Ba = /* @__PURE__ */ M("ZodRecord", (t, e) => {
  Xp.init(t, e), Oe.init(t, e), t._zod.processJSONSchema = (r, n, s) => rd(t, r, n, s), t.keyType = e.keyType, t.valueType = e.valueType;
});
function Ce(t, e, r) {
  return !e || !e._zod ? new Ba({
    type: "record",
    keyType: T(),
    valueType: t,
    ...Q(e)
  }) : new Ba({
    type: "record",
    keyType: t,
    valueType: e,
    ...Q(r)
  });
}
const Vi = /* @__PURE__ */ M("ZodEnum", (t, e) => {
  em.init(t, e), Oe.init(t, e), t._zod.processJSONSchema = (n, s, i) => Gl(t, n, s), t.enum = e.entries, t.options = Object.values(e.entries);
  const r = new Set(Object.keys(e.entries));
  t.extract = (n, s) => {
    const i = {};
    for (const o of n)
      if (r.has(o))
        i[o] = e.entries[o];
      else
        throw new Error(`Key ${o} not found in enum`);
    return new Vi({
      ...e,
      checks: [],
      ...Q(s),
      entries: i
    });
  }, t.exclude = (n, s) => {
    const i = { ...e.entries };
    for (const o of n)
      if (r.has(o))
        delete i[o];
      else
        throw new Error(`Key ${o} not found in enum`);
    return new Vi({
      ...e,
      checks: [],
      ...Q(s),
      entries: i
    });
  };
});
function st(t, e) {
  const r = Array.isArray(t) ? Object.fromEntries(t.map((n) => [n, n])) : t;
  return new Vi({
    type: "enum",
    entries: r,
    ...Q(e)
  });
}
const k_ = /* @__PURE__ */ M("ZodLiteral", (t, e) => {
  tm.init(t, e), Oe.init(t, e), t._zod.processJSONSchema = (r, n, s) => Jl(t, r, n), t.values = new Set(e.values), Object.defineProperty(t, "value", {
    get() {
      if (e.values.length > 1)
        throw new Error("This schema contains multiple valid literal values. Use `.values` instead.");
      return e.values[0];
    }
  });
});
function te(t, e) {
  return new k_({
    type: "literal",
    values: Array.isArray(t) ? t : [t],
    ...Q(e)
  });
}
const $_ = /* @__PURE__ */ M("ZodTransform", (t, e) => {
  rm.init(t, e), Oe.init(t, e), t._zod.processJSONSchema = (r, n, s) => Ql(t, r), t._zod.parse = (r, n) => {
    if (n.direction === "backward")
      throw new _l(t.constructor.name);
    r.addIssue = (i) => {
      if (typeof i == "string")
        r.issues.push(en(i, r.value, e));
      else {
        const o = i;
        o.fatal && (o.continue = !1), o.code ?? (o.code = "custom"), o.input ?? (o.input = r.value), o.inst ?? (o.inst = t), r.issues.push(en(o));
      }
    };
    const s = e.transform(r.value, r);
    return s instanceof Promise ? s.then((i) => (r.value = i, r.fallback = !0, r)) : (r.value = s, r.fallback = !0, r);
  };
});
function gd(t) {
  return new $_({
    type: "transform",
    transform: t
  });
}
const Lo = /* @__PURE__ */ M("ZodOptional", (t, e) => {
  jl.init(t, e), Oe.init(t, e), t._zod.processJSONSchema = (r, n, s) => qo(t, r, n, s), t.unwrap = () => t._zod.def.innerType;
});
function xe(t) {
  return new Lo({
    type: "optional",
    innerType: t
  });
}
const E_ = /* @__PURE__ */ M("ZodExactOptional", (t, e) => {
  nm.init(t, e), Oe.init(t, e), t._zod.processJSONSchema = (r, n, s) => qo(t, r, n, s), t.unwrap = () => t._zod.def.innerType;
});
function T_(t) {
  return new E_({
    type: "optional",
    innerType: t
  });
}
const R_ = /* @__PURE__ */ M("ZodNullable", (t, e) => {
  sm.init(t, e), Oe.init(t, e), t._zod.processJSONSchema = (r, n, s) => nd(t, r, n, s), t.unwrap = () => t._zod.def.innerType;
});
function Wa(t) {
  return new R_({
    type: "nullable",
    innerType: t
  });
}
const I_ = /* @__PURE__ */ M("ZodDefault", (t, e) => {
  im.init(t, e), Oe.init(t, e), t._zod.processJSONSchema = (r, n, s) => id(t, r, n, s), t.unwrap = () => t._zod.def.innerType, t.removeDefault = t.unwrap;
});
function P_(t, e) {
  return new I_({
    type: "default",
    innerType: t,
    get defaultValue() {
      return typeof e == "function" ? e() : vl(e);
    }
  });
}
const C_ = /* @__PURE__ */ M("ZodPrefault", (t, e) => {
  om.init(t, e), Oe.init(t, e), t._zod.processJSONSchema = (r, n, s) => od(t, r, n, s), t.unwrap = () => t._zod.def.innerType;
});
function O_(t, e) {
  return new C_({
    type: "prefault",
    innerType: t,
    get defaultValue() {
      return typeof e == "function" ? e() : vl(e);
    }
  });
}
const _d = /* @__PURE__ */ M("ZodNonOptional", (t, e) => {
  am.init(t, e), Oe.init(t, e), t._zod.processJSONSchema = (r, n, s) => sd(t, r, n, s), t.unwrap = () => t._zod.def.innerType;
});
function A_(t, e) {
  return new _d({
    type: "nonoptional",
    innerType: t,
    ...Q(e)
  });
}
const N_ = /* @__PURE__ */ M("ZodCatch", (t, e) => {
  cm.init(t, e), Oe.init(t, e), t._zod.processJSONSchema = (r, n, s) => ad(t, r, n, s), t.unwrap = () => t._zod.def.innerType, t.removeCatch = t.unwrap;
});
function x_(t, e) {
  return new N_({
    type: "catch",
    innerType: t,
    catchValue: typeof e == "function" ? e : () => e
  });
}
const yd = /* @__PURE__ */ M("ZodPipe", (t, e) => {
  Ml.init(t, e), Oe.init(t, e), t._zod.processJSONSchema = (r, n, s) => cd(t, r, n, s), t.in = e.in, t.out = e.out;
});
function Ga(t, e) {
  return new yd({
    type: "pipe",
    in: t,
    out: e
    // ...util.normalizeParams(params),
  });
}
const z_ = /* @__PURE__ */ M("ZodPreprocess", (t, e) => {
  yd.init(t, e), um.init(t, e);
}), j_ = /* @__PURE__ */ M("ZodReadonly", (t, e) => {
  lm.init(t, e), Oe.init(t, e), t._zod.processJSONSchema = (r, n, s) => ud(t, r, n, s), t.unwrap = () => t._zod.def.innerType;
});
function M_(t) {
  return new j_({
    type: "readonly",
    innerType: t
  });
}
const wd = /* @__PURE__ */ M("ZodCustom", (t, e) => {
  dm.init(t, e), Oe.init(t, e), t._zod.processJSONSchema = (r, n, s) => Kl(t, r);
});
function q_(t, e) {
  return /* @__PURE__ */ ig(wd, t ?? (() => !0), e);
}
function U_(t, e = {}) {
  return /* @__PURE__ */ og(wd, t, e);
}
function D_(t, e) {
  return /* @__PURE__ */ ag(t, e);
}
function vd(t, e) {
  return new z_({
    type: "pipe",
    in: gd(t),
    out: e
  });
}
const L_ = {
  custom: "custom"
};
function Z_(t) {
  return /* @__PURE__ */ Dm(Uo, t);
}
const mn = "2025-11-25", bd = [mn, "2025-06-18", "2025-03-26", "2024-11-05", "2024-10-07"], Yt = "io.modelcontextprotocol/related-task", Qs = "2.0", De = q_((t) => t !== null && (typeof t == "object" || typeof t == "function")), Sd = Ne([T(), ve().int()]), kd = T();
Be({
  /**
   * Requested duration in milliseconds to retain task from creation.
   */
  ttl: ve().optional(),
  /**
   * Time in milliseconds to wait between task status requests.
   */
  pollInterval: ve().optional()
});
const H_ = W({
  ttl: ve().optional()
}), F_ = W({
  taskId: T()
}), Zo = Be({
  /**
   * If specified, the caller is requesting out-of-band progress notifications for this request (as represented by notifications/progress). The value of this parameter is an opaque token that will be attached to any subsequent notifications. The receiver is not obligated to provide these notifications.
   */
  progressToken: Sd.optional(),
  /**
   * If specified, this request is related to the provided task.
   */
  [Yt]: F_.optional()
}), it = W({
  /**
   * See [General fields: `_meta`](/specification/draft/basic/index#meta) for notes on `_meta` usage.
   */
  _meta: Zo.optional()
}), gn = it.extend({
  /**
   * If specified, the caller is requesting task-augmented execution for this request.
   * The request will return a CreateTaskResult immediately, and the actual result can be
   * retrieved later via tasks/result.
   *
   * Task augmentation is subject to capability negotiation - receivers MUST declare support
   * for task augmentation of specific request types in their capabilities.
   */
  task: H_.optional()
}), V_ = (t) => gn.safeParse(t).success, We = W({
  method: T(),
  params: it.loose().optional()
}), ut = W({
  /**
   * See [MCP specification](https://github.com/modelcontextprotocol/modelcontextprotocol/blob/47339c03c143bb4ec01a26e721a1b8fe66634ebe/docs/specification/draft/basic/index.mdx#general-fields)
   * for notes on _meta usage.
   */
  _meta: Zo.optional()
}), lt = W({
  method: T(),
  params: ut.loose().optional()
}), Ge = Be({
  /**
   * See [MCP specification](https://github.com/modelcontextprotocol/modelcontextprotocol/blob/47339c03c143bb4ec01a26e721a1b8fe66634ebe/docs/specification/draft/basic/index.mdx#general-fields)
   * for notes on _meta usage.
   */
  _meta: Zo.optional()
}), Ys = Ne([T(), ve().int()]), $d = W({
  jsonrpc: te(Qs),
  id: Ys,
  ...We.shape
}).strict(), Bi = (t) => $d.safeParse(t).success, Ed = W({
  jsonrpc: te(Qs),
  ...lt.shape
}).strict(), B_ = (t) => Ed.safeParse(t).success, Ho = W({
  jsonrpc: te(Qs),
  id: Ys,
  result: Ge
}).strict(), Wr = (t) => Ho.safeParse(t).success;
var K;
(function(t) {
  t[t.ConnectionClosed = -32e3] = "ConnectionClosed", t[t.RequestTimeout = -32001] = "RequestTimeout", t[t.ParseError = -32700] = "ParseError", t[t.InvalidRequest = -32600] = "InvalidRequest", t[t.MethodNotFound = -32601] = "MethodNotFound", t[t.InvalidParams = -32602] = "InvalidParams", t[t.InternalError = -32603] = "InternalError", t[t.UrlElicitationRequired = -32042] = "UrlElicitationRequired";
})(K || (K = {}));
const Fo = W({
  jsonrpc: te(Qs),
  id: Ys.optional(),
  error: W({
    /**
     * The error type that occurred.
     */
    code: ve().int(),
    /**
     * A short description of the error. The message SHOULD be limited to a concise single sentence.
     */
    message: T(),
    /**
     * Additional information about the error. The value of this member is defined by the sender (e.g. detailed error information, nested errors etc.).
     */
    data: ze().optional()
  })
}).strict(), W_ = (t) => Fo.safeParse(t).success, ms = Ne([
  $d,
  Ed,
  Ho,
  Fo
]);
Ne([Ho, Fo]);
const Xt = Ge.strict(), G_ = ut.extend({
  /**
   * The ID of the request to cancel.
   *
   * This MUST correspond to the ID of a request previously issued in the same direction.
   */
  requestId: Ys.optional(),
  /**
   * An optional string describing the reason for the cancellation. This MAY be logged or presented to the user.
   */
  reason: T().optional()
}), Vo = lt.extend({
  method: te("notifications/cancelled"),
  params: G_
}), J_ = W({
  /**
   * URL or data URI for the icon.
   */
  src: T(),
  /**
   * Optional MIME type for the icon.
   */
  mimeType: T().optional(),
  /**
   * Optional array of strings that specify sizes at which the icon can be used.
   * Each string should be in WxH format (e.g., `"48x48"`, `"96x96"`) or `"any"` for scalable formats like SVG.
   *
   * If not provided, the client should assume that the icon can be used at any size.
   */
  sizes: B(T()).optional(),
  /**
   * Optional specifier for the theme this icon is designed for. `light` indicates
   * the icon is designed to be used with a light background, and `dark` indicates
   * the icon is designed to be used with a dark background.
   *
   * If not provided, the client should assume the icon can be used with any theme.
   */
  theme: st(["light", "dark"]).optional()
}), _n = W({
  /**
   * Optional set of sized icons that the client can display in a user interface.
   *
   * Clients that support rendering icons MUST support at least the following MIME types:
   * - `image/png` - PNG images (safe, universal compatibility)
   * - `image/jpeg` (and `image/jpg`) - JPEG images (safe, universal compatibility)
   *
   * Clients that support rendering icons SHOULD also support:
   * - `image/svg+xml` - SVG images (scalable but requires security precautions)
   * - `image/webp` - WebP images (modern, efficient format)
   */
  icons: B(J_).optional()
}), $r = W({
  /** Intended for programmatic or logical use, but used as a display name in past specs or fallback */
  name: T(),
  /**
   * Intended for UI and end-user contexts — optimized to be human-readable and easily understood,
   * even by those unfamiliar with domain-specific terminology.
   *
   * If not provided, the name should be used for display (except for Tool,
   * where `annotations.title` should be given precedence over using `name`,
   * if present).
   */
  title: T().optional()
}), Td = $r.extend({
  ...$r.shape,
  ..._n.shape,
  version: T(),
  /**
   * An optional URL of the website for this implementation.
   */
  websiteUrl: T().optional(),
  /**
   * An optional human-readable description of what this implementation does.
   *
   * This can be used by clients or servers to provide context about their purpose
   * and capabilities. For example, a server might describe the types of resources
   * or tools it provides, while a client might describe its intended use case.
   */
  description: T().optional()
}), K_ = Do(W({
  applyDefaults: Pe().optional()
}), Ce(T(), ze())), Q_ = vd((t) => t && typeof t == "object" && !Array.isArray(t) && Object.keys(t).length === 0 ? { form: {} } : t, Do(W({
  form: K_.optional(),
  url: De.optional()
}), Ce(T(), ze()).optional())), Y_ = Be({
  /**
   * Present if the client supports listing tasks.
   */
  list: De.optional(),
  /**
   * Present if the client supports cancelling tasks.
   */
  cancel: De.optional(),
  /**
   * Capabilities for task creation on specific request types.
   */
  requests: Be({
    /**
     * Task support for sampling requests.
     */
    sampling: Be({
      createMessage: De.optional()
    }).optional(),
    /**
     * Task support for elicitation requests.
     */
    elicitation: Be({
      create: De.optional()
    }).optional()
  }).optional()
}), X_ = Be({
  /**
   * Present if the server supports listing tasks.
   */
  list: De.optional(),
  /**
   * Present if the server supports cancelling tasks.
   */
  cancel: De.optional(),
  /**
   * Capabilities for task creation on specific request types.
   */
  requests: Be({
    /**
     * Task support for tool requests.
     */
    tools: Be({
      call: De.optional()
    }).optional()
  }).optional()
}), ey = W({
  /**
   * Experimental, non-standard capabilities that the client supports.
   */
  experimental: Ce(T(), De).optional(),
  /**
   * Present if the client supports sampling from an LLM.
   */
  sampling: W({
    /**
     * Present if the client supports context inclusion via includeContext parameter.
     * If not declared, servers SHOULD only use `includeContext: "none"` (or omit it).
     */
    context: De.optional(),
    /**
     * Present if the client supports tool use via tools and toolChoice parameters.
     */
    tools: De.optional()
  }).optional(),
  /**
   * Present if the client supports eliciting user input.
   */
  elicitation: Q_.optional(),
  /**
   * Present if the client supports listing roots.
   */
  roots: W({
    /**
     * Whether the client supports issuing notifications for changes to the roots list.
     */
    listChanged: Pe().optional()
  }).optional(),
  /**
   * Present if the client supports task creation.
   */
  tasks: Y_.optional(),
  /**
   * Extensions that the client supports. Keys are extension identifiers (vendor-prefix/extension-name).
   */
  extensions: Ce(T(), De).optional()
}), ty = it.extend({
  /**
   * The latest version of the Model Context Protocol that the client supports. The client MAY decide to support older versions as well.
   */
  protocolVersion: T(),
  capabilities: ey,
  clientInfo: Td
}), Rd = We.extend({
  method: te("initialize"),
  params: ty
}), ry = W({
  /**
   * Experimental, non-standard capabilities that the server supports.
   */
  experimental: Ce(T(), De).optional(),
  /**
   * Present if the server supports sending log messages to the client.
   */
  logging: De.optional(),
  /**
   * Present if the server supports sending completions to the client.
   */
  completions: De.optional(),
  /**
   * Present if the server offers any prompt templates.
   */
  prompts: W({
    /**
     * Whether this server supports issuing notifications for changes to the prompt list.
     */
    listChanged: Pe().optional()
  }).optional(),
  /**
   * Present if the server offers any resources to read.
   */
  resources: W({
    /**
     * Whether this server supports clients subscribing to resource updates.
     */
    subscribe: Pe().optional(),
    /**
     * Whether this server supports issuing notifications for changes to the resource list.
     */
    listChanged: Pe().optional()
  }).optional(),
  /**
   * Present if the server offers any tools to call.
   */
  tools: W({
    /**
     * Whether this server supports issuing notifications for changes to the tool list.
     */
    listChanged: Pe().optional()
  }).optional(),
  /**
   * Present if the server supports task creation.
   */
  tasks: X_.optional(),
  /**
   * Extensions that the server supports. Keys are extension identifiers (vendor-prefix/extension-name).
   */
  extensions: Ce(T(), De).optional()
}), Id = Ge.extend({
  /**
   * The version of the Model Context Protocol that the server wants to use. This may not match the version that the client requested. If the client cannot support this version, it MUST disconnect.
   */
  protocolVersion: T(),
  capabilities: ry,
  serverInfo: Td,
  /**
   * Instructions describing how to use the server and its features.
   *
   * This can be used by clients to improve the LLM's understanding of available tools, resources, etc. It can be thought of like a "hint" to the model. For example, this information MAY be added to the system prompt.
   */
  instructions: T().optional()
}), Bo = lt.extend({
  method: te("notifications/initialized"),
  params: ut.optional()
}), ny = (t) => Bo.safeParse(t).success, Wo = We.extend({
  method: te("ping"),
  params: it.optional()
}), sy = W({
  /**
   * The progress thus far. This should increase every time progress is made, even if the total is unknown.
   */
  progress: ve(),
  /**
   * Total number of items to process (or total progress required), if known.
   */
  total: xe(ve()),
  /**
   * An optional message describing the current progress.
   */
  message: xe(T())
}), iy = W({
  ...ut.shape,
  ...sy.shape,
  /**
   * The progress token which was given in the initial request, used to associate this notification with the request that is proceeding.
   */
  progressToken: Sd
}), Go = lt.extend({
  method: te("notifications/progress"),
  params: iy
}), oy = it.extend({
  /**
   * An opaque token representing the current pagination position.
   * If provided, the server should return results starting after this cursor.
   */
  cursor: kd.optional()
}), yn = We.extend({
  params: oy.optional()
}), wn = Ge.extend({
  /**
   * An opaque token representing the pagination position after the last returned result.
   * If present, there may be more results available.
   */
  nextCursor: kd.optional()
}), ay = st(["working", "input_required", "completed", "failed", "cancelled"]), vn = W({
  taskId: T(),
  status: ay,
  /**
   * Time in milliseconds to keep task results available after completion.
   * If null, the task has unlimited lifetime until manually cleaned up.
   */
  ttl: Ne([ve(), p_()]),
  /**
   * ISO 8601 timestamp when the task was created.
   */
  createdAt: T(),
  /**
   * ISO 8601 timestamp when the task was last updated.
   */
  lastUpdatedAt: T(),
  pollInterval: xe(ve()),
  /**
   * Optional diagnostic message for failed tasks or other status information.
   */
  statusMessage: xe(T())
}), Er = Ge.extend({
  task: vn
}), cy = ut.merge(vn), Es = lt.extend({
  method: te("notifications/tasks/status"),
  params: cy
}), Jo = We.extend({
  method: te("tasks/get"),
  params: it.extend({
    taskId: T()
  })
}), Ko = Ge.merge(vn), Qo = We.extend({
  method: te("tasks/result"),
  params: it.extend({
    taskId: T()
  })
});
Ge.loose();
const Yo = yn.extend({
  method: te("tasks/list")
}), Xo = wn.extend({
  tasks: B(vn)
}), ea = We.extend({
  method: te("tasks/cancel"),
  params: it.extend({
    taskId: T()
  })
}), uy = Ge.merge(vn), Pd = W({
  /**
   * The URI of this resource.
   */
  uri: T(),
  /**
   * The MIME type of this resource, if known.
   */
  mimeType: xe(T()),
  /**
   * See [MCP specification](https://github.com/modelcontextprotocol/modelcontextprotocol/blob/47339c03c143bb4ec01a26e721a1b8fe66634ebe/docs/specification/draft/basic/index.mdx#general-fields)
   * for notes on _meta usage.
   */
  _meta: Ce(T(), ze()).optional()
}), Cd = Pd.extend({
  /**
   * The text of the item. This must only be set if the item can actually be represented as text (not binary data).
   */
  text: T()
}), ta = T().refine((t) => {
  try {
    return atob(t), !0;
  } catch {
    return !1;
  }
}, { message: "Invalid Base64 string" }), Od = Pd.extend({
  /**
   * A base64-encoded string representing the binary data of the item.
   */
  blob: ta
}), bn = st(["user", "assistant"]), jr = W({
  /**
   * Intended audience(s) for the resource.
   */
  audience: B(bn).optional(),
  /**
   * Importance hint for the resource, from 0 (least) to 1 (most).
   */
  priority: ve().min(0).max(1).optional(),
  /**
   * ISO 8601 timestamp for the most recent modification.
   */
  lastModified: ld({ offset: !0 }).optional()
}), Ad = W({
  ...$r.shape,
  ..._n.shape,
  /**
   * The URI of this resource.
   */
  uri: T(),
  /**
   * A description of what this resource represents.
   *
   * This can be used by clients to improve the LLM's understanding of available resources. It can be thought of like a "hint" to the model.
   */
  description: xe(T()),
  /**
   * The MIME type of this resource, if known.
   */
  mimeType: xe(T()),
  /**
   * The size of the raw resource content, in bytes (i.e., before base64 encoding or any tokenization), if known.
   *
   * This can be used by Hosts to display file sizes and estimate context window usage.
   */
  size: xe(ve()),
  /**
   * Optional annotations for the client.
   */
  annotations: jr.optional(),
  /**
   * See [MCP specification](https://github.com/modelcontextprotocol/modelcontextprotocol/blob/47339c03c143bb4ec01a26e721a1b8fe66634ebe/docs/specification/draft/basic/index.mdx#general-fields)
   * for notes on _meta usage.
   */
  _meta: xe(Be({}))
}), ly = W({
  ...$r.shape,
  ..._n.shape,
  /**
   * A URI template (according to RFC 6570) that can be used to construct resource URIs.
   */
  uriTemplate: T(),
  /**
   * A description of what this template is for.
   *
   * This can be used by clients to improve the LLM's understanding of available resources. It can be thought of like a "hint" to the model.
   */
  description: xe(T()),
  /**
   * The MIME type for all resources that match this template. This should only be included if all resources matching this template have the same type.
   */
  mimeType: xe(T()),
  /**
   * Optional annotations for the client.
   */
  annotations: jr.optional(),
  /**
   * See [MCP specification](https://github.com/modelcontextprotocol/modelcontextprotocol/blob/47339c03c143bb4ec01a26e721a1b8fe66634ebe/docs/specification/draft/basic/index.mdx#general-fields)
   * for notes on _meta usage.
   */
  _meta: xe(Be({}))
}), Wi = yn.extend({
  method: te("resources/list")
}), Nd = wn.extend({
  resources: B(Ad)
}), Gi = yn.extend({
  method: te("resources/templates/list")
}), xd = wn.extend({
  resourceTemplates: B(ly)
}), ra = it.extend({
  /**
   * The URI of the resource to read. The URI can use any protocol; it is up to the server how to interpret it.
   *
   * @format uri
   */
  uri: T()
}), dy = ra, Ji = We.extend({
  method: te("resources/read"),
  params: dy
}), zd = Ge.extend({
  contents: B(Ne([Cd, Od]))
}), jd = lt.extend({
  method: te("notifications/resources/list_changed"),
  params: ut.optional()
}), hy = ra, fy = We.extend({
  method: te("resources/subscribe"),
  params: hy
}), py = ra, my = We.extend({
  method: te("resources/unsubscribe"),
  params: py
}), gy = ut.extend({
  /**
   * The URI of the resource that has been updated. This might be a sub-resource of the one that the client actually subscribed to.
   */
  uri: T()
}), _y = lt.extend({
  method: te("notifications/resources/updated"),
  params: gy
}), yy = W({
  /**
   * The name of the argument.
   */
  name: T(),
  /**
   * A human-readable description of the argument.
   */
  description: xe(T()),
  /**
   * Whether this argument must be provided.
   */
  required: xe(Pe())
}), wy = W({
  ...$r.shape,
  ..._n.shape,
  /**
   * An optional description of what this prompt provides
   */
  description: xe(T()),
  /**
   * A list of arguments to use for templating the prompt.
   */
  arguments: xe(B(yy)),
  /**
   * See [MCP specification](https://github.com/modelcontextprotocol/modelcontextprotocol/blob/47339c03c143bb4ec01a26e721a1b8fe66634ebe/docs/specification/draft/basic/index.mdx#general-fields)
   * for notes on _meta usage.
   */
  _meta: xe(Be({}))
}), Ki = yn.extend({
  method: te("prompts/list")
}), Md = wn.extend({
  prompts: B(wy)
}), vy = it.extend({
  /**
   * The name of the prompt or prompt template.
   */
  name: T(),
  /**
   * Arguments to use for templating the prompt.
   */
  arguments: Ce(T(), T()).optional()
}), Qi = We.extend({
  method: te("prompts/get"),
  params: vy
}), na = W({
  type: te("text"),
  /**
   * The text content of the message.
   */
  text: T(),
  /**
   * Optional annotations for the client.
   */
  annotations: jr.optional(),
  /**
   * See [MCP specification](https://github.com/modelcontextprotocol/modelcontextprotocol/blob/47339c03c143bb4ec01a26e721a1b8fe66634ebe/docs/specification/draft/basic/index.mdx#general-fields)
   * for notes on _meta usage.
   */
  _meta: Ce(T(), ze()).optional()
}), sa = W({
  type: te("image"),
  /**
   * The base64-encoded image data.
   */
  data: ta,
  /**
   * The MIME type of the image. Different providers may support different image types.
   */
  mimeType: T(),
  /**
   * Optional annotations for the client.
   */
  annotations: jr.optional(),
  /**
   * See [MCP specification](https://github.com/modelcontextprotocol/modelcontextprotocol/blob/47339c03c143bb4ec01a26e721a1b8fe66634ebe/docs/specification/draft/basic/index.mdx#general-fields)
   * for notes on _meta usage.
   */
  _meta: Ce(T(), ze()).optional()
}), ia = W({
  type: te("audio"),
  /**
   * The base64-encoded audio data.
   */
  data: ta,
  /**
   * The MIME type of the audio. Different providers may support different audio types.
   */
  mimeType: T(),
  /**
   * Optional annotations for the client.
   */
  annotations: jr.optional(),
  /**
   * See [MCP specification](https://github.com/modelcontextprotocol/modelcontextprotocol/blob/47339c03c143bb4ec01a26e721a1b8fe66634ebe/docs/specification/draft/basic/index.mdx#general-fields)
   * for notes on _meta usage.
   */
  _meta: Ce(T(), ze()).optional()
}), by = W({
  type: te("tool_use"),
  /**
   * The name of the tool to invoke.
   * Must match a tool name from the request's tools array.
   */
  name: T(),
  /**
   * Unique identifier for this tool call.
   * Used to correlate with ToolResultContent in subsequent messages.
   */
  id: T(),
  /**
   * Arguments to pass to the tool.
   * Must conform to the tool's inputSchema.
   */
  input: Ce(T(), ze()),
  /**
   * See [MCP specification](https://github.com/modelcontextprotocol/modelcontextprotocol/blob/47339c03c143bb4ec01a26e721a1b8fe66634ebe/docs/specification/draft/basic/index.mdx#general-fields)
   * for notes on _meta usage.
   */
  _meta: Ce(T(), ze()).optional()
}), Sy = W({
  type: te("resource"),
  resource: Ne([Cd, Od]),
  /**
   * Optional annotations for the client.
   */
  annotations: jr.optional(),
  /**
   * See [MCP specification](https://github.com/modelcontextprotocol/modelcontextprotocol/blob/47339c03c143bb4ec01a26e721a1b8fe66634ebe/docs/specification/draft/basic/index.mdx#general-fields)
   * for notes on _meta usage.
   */
  _meta: Ce(T(), ze()).optional()
}), ky = Ad.extend({
  type: te("resource_link")
}), oa = Ne([
  na,
  sa,
  ia,
  ky,
  Sy
]), $y = W({
  role: bn,
  content: oa
}), qd = Ge.extend({
  /**
   * An optional description for the prompt.
   */
  description: T().optional(),
  messages: B($y)
}), Ud = lt.extend({
  method: te("notifications/prompts/list_changed"),
  params: ut.optional()
}), Ey = W({
  /**
   * A human-readable title for the tool.
   */
  title: T().optional(),
  /**
   * If true, the tool does not modify its environment.
   *
   * Default: false
   */
  readOnlyHint: Pe().optional(),
  /**
   * If true, the tool may perform destructive updates to its environment.
   * If false, the tool performs only additive updates.
   *
   * (This property is meaningful only when `readOnlyHint == false`)
   *
   * Default: true
   */
  destructiveHint: Pe().optional(),
  /**
   * If true, calling the tool repeatedly with the same arguments
   * will have no additional effect on the its environment.
   *
   * (This property is meaningful only when `readOnlyHint == false`)
   *
   * Default: false
   */
  idempotentHint: Pe().optional(),
  /**
   * If true, this tool may interact with an "open world" of external
   * entities. If false, the tool's domain of interaction is closed.
   * For example, the world of a web search tool is open, whereas that
   * of a memory tool is not.
   *
   * Default: true
   */
  openWorldHint: Pe().optional()
}), Ty = W({
  /**
   * Indicates the tool's preference for task-augmented execution.
   * - "required": Clients MUST invoke the tool as a task
   * - "optional": Clients MAY invoke the tool as a task or normal request
   * - "forbidden": Clients MUST NOT attempt to invoke the tool as a task
   *
   * If not present, defaults to "forbidden".
   */
  taskSupport: st(["required", "optional", "forbidden"]).optional()
}), Dd = W({
  ...$r.shape,
  ..._n.shape,
  /**
   * A human-readable description of the tool.
   */
  description: T().optional(),
  /**
   * A JSON Schema 2020-12 object defining the expected parameters for the tool.
   * Must have type: 'object' at the root level per MCP spec.
   */
  inputSchema: W({
    type: te("object"),
    properties: Ce(T(), De).optional(),
    required: B(T()).optional()
  }).catchall(ze()),
  /**
   * An optional JSON Schema 2020-12 object defining the structure of the tool's output
   * returned in the structuredContent field of a CallToolResult.
   * Must have type: 'object' at the root level per MCP spec.
   */
  outputSchema: W({
    type: te("object"),
    properties: Ce(T(), De).optional(),
    required: B(T()).optional()
  }).catchall(ze()).optional(),
  /**
   * Optional additional tool information.
   */
  annotations: Ey.optional(),
  /**
   * Execution-related properties for this tool.
   */
  execution: Ty.optional(),
  /**
   * See [MCP specification](https://github.com/modelcontextprotocol/modelcontextprotocol/blob/47339c03c143bb4ec01a26e721a1b8fe66634ebe/docs/specification/draft/basic/index.mdx#general-fields)
   * for notes on _meta usage.
   */
  _meta: Ce(T(), ze()).optional()
}), Ts = yn.extend({
  method: te("tools/list")
}), Ld = wn.extend({
  tools: B(Dd)
}), Sn = Ge.extend({
  /**
   * A list of content objects that represent the result of the tool call.
   *
   * If the Tool does not define an outputSchema, this field MUST be present in the result.
   * For backwards compatibility, this field is always present, but it may be empty.
   */
  content: B(oa).default([]),
  /**
   * An object containing structured tool output.
   *
   * If the Tool defines an outputSchema, this field MUST be present in the result, and contain a JSON object that matches the schema.
   */
  structuredContent: Ce(T(), ze()).optional(),
  /**
   * Whether the tool call ended in an error.
   *
   * If not set, this is assumed to be false (the call was successful).
   *
   * Any errors that originate from the tool SHOULD be reported inside the result
   * object, with `isError` set to true, _not_ as an MCP protocol-level error
   * response. Otherwise, the LLM would not be able to see that an error occurred
   * and self-correct.
   *
   * However, any errors in _finding_ the tool, an error indicating that the
   * server does not support tool calls, or any other exceptional conditions,
   * should be reported as an MCP error response.
   */
  isError: Pe().optional()
});
Sn.or(Ge.extend({
  toolResult: ze()
}));
const Ry = gn.extend({
  /**
   * The name of the tool to call.
   */
  name: T(),
  /**
   * Arguments to pass to the tool.
   */
  arguments: Ce(T(), ze()).optional()
}), tn = We.extend({
  method: te("tools/call"),
  params: Ry
}), Zd = lt.extend({
  method: te("notifications/tools/list_changed"),
  params: ut.optional()
}), Iy = W({
  /**
   * If true, the list will be refreshed automatically when a list changed notification is received.
   * The callback will be called with the updated list.
   *
   * If false, the callback will be called with null items, allowing manual refresh.
   *
   * @default true
   */
  autoRefresh: Pe().default(!0),
  /**
   * Debounce time in milliseconds for list changed notification processing.
   *
   * Multiple notifications received within this timeframe will only trigger one refresh.
   * Set to 0 to disable debouncing.
   *
   * @default 300
   */
  debounceMs: ve().int().nonnegative().default(300)
}), Rs = st(["debug", "info", "notice", "warning", "error", "critical", "alert", "emergency"]), Py = it.extend({
  /**
   * The level of logging that the client wants to receive from the server. The server should send all logs at this level and higher (i.e., more severe) to the client as notifications/logging/message.
   */
  level: Rs
}), Hd = We.extend({
  method: te("logging/setLevel"),
  params: Py
}), Cy = ut.extend({
  /**
   * The severity of this log message.
   */
  level: Rs,
  /**
   * An optional name of the logger issuing this message.
   */
  logger: T().optional(),
  /**
   * The data to be logged, such as a string message or an object. Any JSON serializable type is allowed here.
   */
  data: ze()
}), Oy = lt.extend({
  method: te("notifications/message"),
  params: Cy
}), Ay = W({
  /**
   * A hint for a model name.
   */
  name: T().optional()
}), Ny = W({
  /**
   * Optional hints to use for model selection.
   */
  hints: B(Ay).optional(),
  /**
   * How much to prioritize cost when selecting a model.
   */
  costPriority: ve().min(0).max(1).optional(),
  /**
   * How much to prioritize sampling speed (latency) when selecting a model.
   */
  speedPriority: ve().min(0).max(1).optional(),
  /**
   * How much to prioritize intelligence and capabilities when selecting a model.
   */
  intelligencePriority: ve().min(0).max(1).optional()
}), xy = W({
  /**
   * Controls when tools are used:
   * - "auto": Model decides whether to use tools (default)
   * - "required": Model MUST use at least one tool before completing
   * - "none": Model MUST NOT use any tools
   */
  mode: st(["auto", "required", "none"]).optional()
}), zy = W({
  type: te("tool_result"),
  toolUseId: T().describe("The unique identifier for the corresponding tool call."),
  content: B(oa).default([]),
  structuredContent: W({}).loose().optional(),
  isError: Pe().optional(),
  /**
   * See [MCP specification](https://github.com/modelcontextprotocol/modelcontextprotocol/blob/47339c03c143bb4ec01a26e721a1b8fe66634ebe/docs/specification/draft/basic/index.mdx#general-fields)
   * for notes on _meta usage.
   */
  _meta: Ce(T(), ze()).optional()
}), jy = md("type", [na, sa, ia]), Is = md("type", [
  na,
  sa,
  ia,
  by,
  zy
]), My = W({
  role: bn,
  content: Ne([Is, B(Is)]),
  /**
   * See [MCP specification](https://github.com/modelcontextprotocol/modelcontextprotocol/blob/47339c03c143bb4ec01a26e721a1b8fe66634ebe/docs/specification/draft/basic/index.mdx#general-fields)
   * for notes on _meta usage.
   */
  _meta: Ce(T(), ze()).optional()
}), qy = gn.extend({
  messages: B(My),
  /**
   * The server's preferences for which model to select. The client MAY modify or omit this request.
   */
  modelPreferences: Ny.optional(),
  /**
   * An optional system prompt the server wants to use for sampling. The client MAY modify or omit this prompt.
   */
  systemPrompt: T().optional(),
  /**
   * A request to include context from one or more MCP servers (including the caller), to be attached to the prompt.
   * The client MAY ignore this request.
   *
   * Default is "none". Values "thisServer" and "allServers" are soft-deprecated. Servers SHOULD only use these values if the client
   * declares ClientCapabilities.sampling.context. These values may be removed in future spec releases.
   */
  includeContext: st(["none", "thisServer", "allServers"]).optional(),
  temperature: ve().optional(),
  /**
   * The requested maximum number of tokens to sample (to prevent runaway completions).
   *
   * The client MAY choose to sample fewer tokens than the requested maximum.
   */
  maxTokens: ve().int(),
  stopSequences: B(T()).optional(),
  /**
   * Optional metadata to pass through to the LLM provider. The format of this metadata is provider-specific.
   */
  metadata: De.optional(),
  /**
   * Tools that the model may use during generation.
   * The client MUST return an error if this field is provided but ClientCapabilities.sampling.tools is not declared.
   */
  tools: B(Dd).optional(),
  /**
   * Controls how the model uses tools.
   * The client MUST return an error if this field is provided but ClientCapabilities.sampling.tools is not declared.
   * Default is `{ mode: "auto" }`.
   */
  toolChoice: xy.optional()
}), Fd = We.extend({
  method: te("sampling/createMessage"),
  params: qy
}), Xs = Ge.extend({
  /**
   * The name of the model that generated the message.
   */
  model: T(),
  /**
   * The reason why sampling stopped, if known.
   *
   * Standard values:
   * - "endTurn": Natural end of the assistant's turn
   * - "stopSequence": A stop sequence was encountered
   * - "maxTokens": Maximum token limit was reached
   *
   * This field is an open string to allow for provider-specific stop reasons.
   */
  stopReason: xe(st(["endTurn", "stopSequence", "maxTokens"]).or(T())),
  role: bn,
  /**
   * Response content. Single content block (text, image, or audio).
   */
  content: jy
}), aa = Ge.extend({
  /**
   * The name of the model that generated the message.
   */
  model: T(),
  /**
   * The reason why sampling stopped, if known.
   *
   * Standard values:
   * - "endTurn": Natural end of the assistant's turn
   * - "stopSequence": A stop sequence was encountered
   * - "maxTokens": Maximum token limit was reached
   * - "toolUse": The model wants to use one or more tools
   *
   * This field is an open string to allow for provider-specific stop reasons.
   */
  stopReason: xe(st(["endTurn", "stopSequence", "maxTokens", "toolUse"]).or(T())),
  role: bn,
  /**
   * Response content. May be a single block or array. May include ToolUseContent if stopReason is "toolUse".
   */
  content: Ne([Is, B(Is)])
}), Uy = W({
  type: te("boolean"),
  title: T().optional(),
  description: T().optional(),
  default: Pe().optional()
}), Dy = W({
  type: te("string"),
  title: T().optional(),
  description: T().optional(),
  minLength: ve().optional(),
  maxLength: ve().optional(),
  format: st(["email", "uri", "date", "date-time"]).optional(),
  default: T().optional()
}), Ly = W({
  type: st(["number", "integer"]),
  title: T().optional(),
  description: T().optional(),
  minimum: ve().optional(),
  maximum: ve().optional(),
  default: ve().optional()
}), Zy = W({
  type: te("string"),
  title: T().optional(),
  description: T().optional(),
  enum: B(T()),
  default: T().optional()
}), Hy = W({
  type: te("string"),
  title: T().optional(),
  description: T().optional(),
  oneOf: B(W({
    const: T(),
    title: T()
  })),
  default: T().optional()
}), Fy = W({
  type: te("string"),
  title: T().optional(),
  description: T().optional(),
  enum: B(T()),
  enumNames: B(T()).optional(),
  default: T().optional()
}), Vy = Ne([Zy, Hy]), By = W({
  type: te("array"),
  title: T().optional(),
  description: T().optional(),
  minItems: ve().optional(),
  maxItems: ve().optional(),
  items: W({
    type: te("string"),
    enum: B(T())
  }),
  default: B(T()).optional()
}), Wy = W({
  type: te("array"),
  title: T().optional(),
  description: T().optional(),
  minItems: ve().optional(),
  maxItems: ve().optional(),
  items: W({
    anyOf: B(W({
      const: T(),
      title: T()
    }))
  }),
  default: B(T()).optional()
}), Gy = Ne([By, Wy]), Jy = Ne([Fy, Vy, Gy]), Ky = Ne([Jy, Uy, Dy, Ly]), Qy = gn.extend({
  /**
   * The elicitation mode.
   *
   * Optional for backward compatibility. Clients MUST treat missing mode as "form".
   */
  mode: te("form").optional(),
  /**
   * The message to present to the user describing what information is being requested.
   */
  message: T(),
  /**
   * A restricted subset of JSON Schema.
   * Only top-level properties are allowed, without nesting.
   */
  requestedSchema: W({
    type: te("object"),
    properties: Ce(T(), Ky),
    required: B(T()).optional()
  })
}), Yy = gn.extend({
  /**
   * The elicitation mode.
   */
  mode: te("url"),
  /**
   * The message to present to the user explaining why the interaction is needed.
   */
  message: T(),
  /**
   * The ID of the elicitation, which must be unique within the context of the server.
   * The client MUST treat this ID as an opaque value.
   */
  elicitationId: T(),
  /**
   * The URL that the user should navigate to.
   */
  url: T().url()
}), Xy = Ne([Qy, Yy]), Vd = We.extend({
  method: te("elicitation/create"),
  params: Xy
}), ew = ut.extend({
  /**
   * The ID of the elicitation that completed.
   */
  elicitationId: T()
}), tw = lt.extend({
  method: te("notifications/elicitation/complete"),
  params: ew
}), rn = Ge.extend({
  /**
   * The user action in response to the elicitation.
   * - "accept": User submitted the form/confirmed the action
   * - "decline": User explicitly decline the action
   * - "cancel": User dismissed without making an explicit choice
   */
  action: st(["accept", "decline", "cancel"]),
  /**
   * The submitted form data, only present when action is "accept".
   * Contains values matching the requested schema.
   * Per MCP spec, content is "typically omitted" for decline/cancel actions.
   * We normalize null to undefined for leniency while maintaining type compatibility.
   */
  content: vd((t) => t === null ? void 0 : t, Ce(T(), Ne([T(), ve(), Pe(), B(T())])).optional())
}), rw = W({
  type: te("ref/resource"),
  /**
   * The URI or URI template of the resource.
   */
  uri: T()
}), nw = W({
  type: te("ref/prompt"),
  /**
   * The name of the prompt or prompt template
   */
  name: T()
}), sw = it.extend({
  ref: Ne([nw, rw]),
  /**
   * The argument's information
   */
  argument: W({
    /**
     * The name of the argument
     */
    name: T(),
    /**
     * The value of the argument to use for completion matching.
     */
    value: T()
  }),
  context: W({
    /**
     * Previously-resolved variables in a URI template or prompt.
     */
    arguments: Ce(T(), T()).optional()
  }).optional()
}), Yi = We.extend({
  method: te("completion/complete"),
  params: sw
});
function iw(t) {
  if (t.params.ref.type !== "ref/prompt")
    throw new TypeError(`Expected CompleteRequestPrompt, but got ${t.params.ref.type}`);
}
function ow(t) {
  if (t.params.ref.type !== "ref/resource")
    throw new TypeError(`Expected CompleteRequestResourceTemplate, but got ${t.params.ref.type}`);
}
const Bd = Ge.extend({
  completion: Be({
    /**
     * An array of completion values. Must not exceed 100 items.
     */
    values: B(T()).max(100),
    /**
     * The total number of completion options available. This can exceed the number of values actually sent in the response.
     */
    total: xe(ve().int()),
    /**
     * Indicates whether there are additional completion options beyond those provided in the current response, even if the exact total is unknown.
     */
    hasMore: xe(Pe())
  })
}), aw = W({
  /**
   * The URI identifying the root. This *must* start with file:// for now.
   */
  uri: T().startsWith("file://"),
  /**
   * An optional name for the root.
   */
  name: T().optional(),
  /**
   * See [MCP specification](https://github.com/modelcontextprotocol/modelcontextprotocol/blob/47339c03c143bb4ec01a26e721a1b8fe66634ebe/docs/specification/draft/basic/index.mdx#general-fields)
   * for notes on _meta usage.
   */
  _meta: Ce(T(), ze()).optional()
}), cw = We.extend({
  method: te("roots/list"),
  params: it.optional()
}), Wd = Ge.extend({
  roots: B(aw)
}), uw = lt.extend({
  method: te("notifications/roots/list_changed"),
  params: ut.optional()
});
Ne([
  Wo,
  Rd,
  Yi,
  Hd,
  Qi,
  Ki,
  Wi,
  Gi,
  Ji,
  fy,
  my,
  tn,
  Ts,
  Jo,
  Qo,
  Yo,
  ea
]);
Ne([
  Vo,
  Go,
  Bo,
  uw,
  Es
]);
Ne([
  Xt,
  Xs,
  aa,
  rn,
  Wd,
  Ko,
  Xo,
  Er
]);
Ne([
  Wo,
  Fd,
  Vd,
  cw,
  Jo,
  Qo,
  Yo,
  ea
]);
Ne([
  Vo,
  Go,
  Oy,
  _y,
  jd,
  Zd,
  Ud,
  Es,
  tw
]);
Ne([
  Xt,
  Id,
  Bd,
  qd,
  Md,
  Nd,
  xd,
  zd,
  Sn,
  Ld,
  Ko,
  Xo,
  Er
]);
class G extends Error {
  constructor(e, r, n) {
    super(`MCP error ${e}: ${r}`), this.code = e, this.data = n, this.name = "McpError";
  }
  /**
   * Factory method to create the appropriate error type based on the error code and data
   */
  static fromError(e, r, n) {
    if (e === K.UrlElicitationRequired && n) {
      const s = n;
      if (s.elicitations)
        return new lw(s.elicitations, r);
    }
    return new G(e, r, n);
  }
}
class lw extends G {
  constructor(e, r = `URL elicitation${e.length > 1 ? "s" : ""} required`) {
    super(K.UrlElicitationRequired, r, {
      elicitations: e
    });
  }
  get elicitations() {
    return this.data?.elicitations ?? [];
  }
}
const Ja = { none: 0, error: 1, warn: 2, info: 3, debug: 4 }, dw = { error: "error", warn: "warn", info: "info", log: "info", debug: "debug" }, hw = (t, e) => Ja[t] <= Ja[e], Xi = (t) => typeof t == "string" ? t : JSON.stringify(t), fw = (t, e) => `${Xi(t)} > ${Xi(e)}`, pw = (t, e) => {
  let r = `[${Xi(t)}]`;
  return typeof window < "u" ? { text: `%c${r}`, style: `color: ${e.color || "#00bcd4"}; font-weight: bold;` } : { text: r };
}, Ur = (t, e, r, n) => (...s) => {
  if (!hw(dw[t], n())) return;
  if (!e) return void console[t](...s);
  let { text: i, style: o } = pw(e, r);
  o ? console[t](i, o, ...s) : console[t](i, ...s);
}, Gd = (t, e) => {
  let r = e.logLevel ?? "debug", n = () => r;
  return { log: Ur("log", t, e, n), info: Ur("info", t, e, n), warn: Ur("warn", t, e, n), error: Ur("error", t, e, n), debug: Ur("debug", t, e, n), setLogLevel: (s) => {
    r = s;
  }, extend: (s) => Gd(t ? fw(t, s) : s, { ...e, logLevel: r }) };
}, ca = (t, e) => Gd(t, { color: "#00bcd4", logLevel: "debug", ...e }), mw = ca("angie-sdk", { color: "#00BCD4", logLevel: "error" }), $t = (t) => mw.extend(t), hi = $t("iframe-utils");
let Gr = null;
const Ka = () => (Gr && document.contains(Gr) || (Gr = document.querySelector('iframe[src*="angie/"]')), Gr), Tr = (t, e) => {
  hi.log("postMessageToAngieIframe", t, e);
  const r = Ka();
  if (!r?.contentWindow) return !1;
  const n = (() => {
    const s = Ka();
    if (!s) return null;
    try {
      return new URL(s.src).origin;
    } catch (i) {
      return hi.error("Error parsing iframe URL:", i), null;
    }
  })();
  return n ? (r.contentWindow.postMessage(t, n), !0) : (hi.error("Could not determine target origin for Angie iframe"), !1);
};
var Qa, Ps, Ya, gr, Ie, Rr, _r;
(function(t) {
  t.POST_MESSAGE = "postMessage";
})(Qa || (Qa = {})), (function(t) {
  t.POST_MESSAGE = "postMessage";
})(Ps || (Ps = {})), (function(t) {
  t.STREAMABLE_HTTP = "streamableHttp", t.SSE = "sse";
})(Ya || (Ya = {})), (function(t) {
  t.LOCAL = "local", t.REMOTE = "remote";
})(gr || (gr = {})), (function(t) {
  t.SDK_ANGIE_READY_PING = "sdk-angie-ready-ping", t.SDK_ANGIE_REFRESH_PING = "sdk-angie-refresh-ping", t.SDK_ANGIE_ALL_SERVERS_REGISTERED = "sdk-angie-all-servers-registered", t.SDK_REQUEST_CLIENT_CREATION = "sdk-request-client-creation", t.SDK_REQUEST_INIT_SERVER = "sdk-request-init-server", t.SDK_TRIGGER_ANGIE = "sdk-trigger-angie", t.SDK_TRIGGER_ANGIE_RESPONSE = "sdk-trigger-angie-response", t.ANGIE_SIDEBAR_RESIZED = "angie-sidebar-resized", t.ANGIE_SIDEBAR_TOGGLED = "angie-sidebar-toggled", t.ANGIE_CHAT_TOGGLE = "angie-chat-toggle", t.ANGIE_STUDIO_TOGGLE = "angie-studio-toggle", t.ANGIE_NAVIGATE_TO_URL = "angie/navigate-to-url", t.ANGIE_PAGE_RELOAD = "angie/page-reload", t.ANGIE_DISABLE_NAVIGATION_PREVENTION = "angie/disable-navigation-prevention", t.ANGIE_NAVIGATE_AFTER_RESPONSE = "angie/navigate-after-response";
})(Ie || (Ie = {})), (function(t) {
  t.SET = "ANGIE_SET_LOCALSTORAGE", t.GET = "ANGIE_GET_LOCALSTORAGE";
})(Rr || (Rr = {})), (function(t) {
  t.RESET_HASH = "reset-hash", t.HOST_READY = "host/ready", t.ANGIE_LOADED = "angie/loaded", t.ANGIE_READY = "angie/ready";
})(_r || (_r = {}));
const gw = $t("angie-detector");
class _w {
  isAngieReady = !1;
  readyPromise;
  readyResolve;
  constructor() {
    if (this.readyPromise = new Promise((n) => {
      this.readyResolve = n;
    }), typeof window > "u") return;
    let e = 0;
    const r = () => {
      if (this.isAngieReady || e >= 500) return void (!this.isAngieReady && e >= 500 && this.handleDetectionTimeout());
      const n = new MessageChannel();
      n.port1.onmessage = (i) => {
        this.handleAngieReady(i.data), n.port1.close(), n.port2.close();
      };
      const s = { type: Ie.SDK_ANGIE_READY_PING, timestamp: Date.now() };
      window.postMessage(s, window.location.origin, [n.port2]), e++, setTimeout(r, 500);
    };
    r();
  }
  handleAngieReady(e) {
    this.isAngieReady = !0;
    const r = { isReady: !0, version: e.version, capabilities: e.capabilities };
    this.readyResolve && this.readyResolve(r);
  }
  handleDetectionTimeout() {
    this.readyResolve && this.readyResolve({ isReady: !1 }), gw.warn("Detection timeout - Angie may not be available");
  }
  isReady() {
    return this.isAngieReady;
  }
  async waitForReady() {
    return this.readyPromise;
  }
  async waitUntilReady(e) {
    return this.isAngieReady ? this.readyPromise : Promise.race([this.readyPromise, new Promise((r) => {
      setTimeout(() => r({ isReady: !1 }), e);
    })]);
  }
}
class yw {
  sessionId;
  onmessage;
  onerror;
  onclose;
  _port;
  _started = !1;
  _closed = !1;
  constructor(e) {
    if (!e) throw new Error("MessagePort is required");
    this._port = e, this._port.onmessage = (r) => {
      try {
        const n = ms.parse(r.data);
        this.onmessage?.(n);
      } catch (n) {
        const s = new Error(`Failed to parse message: ${n}`);
        this.onerror?.(s);
      }
    }, this._port.onmessageerror = (r) => {
      const n = new Error(`MessagePort error: ${JSON.stringify(r)}`);
      this.onerror?.(n);
    };
  }
  async start() {
    if (this._started) throw new Error("BrowserContextTransport already started! If using Client or Server class, note that connect() calls start() automatically.");
    if (this._closed) throw new Error("Cannot start a closed BrowserContextTransport");
    this._started = !0, this._port.start();
  }
  async send(e) {
    if (this._closed) throw new Error("Cannot send on a closed BrowserContextTransport");
    return new Promise((r, n) => {
      try {
        this._port.postMessage(e), r();
      } catch (s) {
        const i = s instanceof Error ? s : new Error(String(s));
        this.onerror?.(i), n(i);
      }
    });
  }
  async close() {
    this._closed || (this._closed = !0, this._port.close(), this.onclose?.());
  }
}
class ww {
  async requestClientCreation(e) {
    const { config: r } = e, n = { serverId: e.id, serverName: r.name, serverTitle: r.title, serverVersion: r.version, description: r.description, transport: r.transport || Ps.POST_MESSAGE, capabilities: r.capabilities, instanceId: e.instanceId };
    return "type" in r && r.type === "remote" && (n.remote = { url: r.url }), new Promise((s, i) => {
      const o = new MessageChannel(), a = setTimeout(() => {
        i(new Error("Client creation request timed out after 15000ms"));
      }, 15e3);
      o.port1.onmessage = (u) => {
        clearTimeout(a), s(u.data);
      };
      const c = { type: Ie.SDK_REQUEST_CLIENT_CREATION, payload: n, timestamp: Date.now() };
      window.postMessage(c, window.location.origin, [o.port2]);
    });
  }
}
const ua = "angie-sidebar-container", ge = { open: !1, iframe: null, iframeUrlObject: null, containerId: ua };
class Jr extends Error {
}
Jr.prototype.name = "InvalidTokenError";
var bt, St, fi, vw = { debug: () => {
}, info: () => {
}, warn: () => {
}, error: () => {
} }, tr = ((t) => (t[t.NONE = 0] = "NONE", t[t.ERROR = 1] = "ERROR", t[t.WARN = 2] = "WARN", t[t.INFO = 3] = "INFO", t[t.DEBUG = 4] = "DEBUG", t))(tr || {});
(fi = tr || (tr = {})).reset = function() {
  bt = 3, St = vw;
}, fi.setLevel = function(t) {
  if (!(0 <= t && t <= 4)) throw new Error("Invalid log level");
  bt = t;
}, fi.setLogger = function(t) {
  St = t;
};
var de = class wt {
  constructor(e) {
    this._name = e;
  }
  debug(...e) {
    bt >= 4 && St.debug(wt._format(this._name, this._method), ...e);
  }
  info(...e) {
    bt >= 3 && St.info(wt._format(this._name, this._method), ...e);
  }
  warn(...e) {
    bt >= 2 && St.warn(wt._format(this._name, this._method), ...e);
  }
  error(...e) {
    bt >= 1 && St.error(wt._format(this._name, this._method), ...e);
  }
  throw(e) {
    throw this.error(e), e;
  }
  create(e) {
    const r = Object.create(this);
    return r._method = e, r.debug("begin"), r;
  }
  static createStatic(e, r) {
    const n = new wt(`${e}.${r}`);
    return n.debug("begin"), n;
  }
  static _format(e, r) {
    const n = `[${e}]`;
    return r ? `${n} ${r}:` : n;
  }
  static debug(e, ...r) {
    bt >= 4 && St.debug(wt._format(e), ...r);
  }
  static info(e, ...r) {
    bt >= 3 && St.info(wt._format(e), ...r);
  }
  static warn(e, ...r) {
    bt >= 2 && St.warn(wt._format(e), ...r);
  }
  static error(e, ...r) {
    bt >= 1 && St.error(wt._format(e), ...r);
  }
};
tr.reset();
var nn = class {
  static decode(t) {
    try {
      return (function(e, r) {
        if (typeof e != "string") throw new Jr("Invalid token specified: must be a string");
        r || (r = {});
        const n = r.header === !0 ? 0 : 1, s = e.split(".")[n];
        if (typeof s != "string") throw new Jr(`Invalid token specified: missing part #${n + 1}`);
        let i;
        try {
          i = (function(o) {
            let a = o.replace(/-/g, "+").replace(/_/g, "/");
            switch (a.length % 4) {
              case 0:
                break;
              case 2:
                a += "==";
                break;
              case 3:
                a += "=";
                break;
              default:
                throw new Error("base64 string is not of the correct length");
            }
            try {
              return (function(c) {
                return decodeURIComponent(atob(c).replace(/(.)/g, (u, l) => {
                  let h = l.charCodeAt(0).toString(16).toUpperCase();
                  return h.length < 2 && (h = "0" + h), "%" + h;
                }));
              })(a);
            } catch {
              return atob(a);
            }
          })(s);
        } catch (o) {
          throw new Jr(`Invalid token specified: invalid base64 for part #${n + 1} (${o.message})`);
        }
        try {
          return JSON.parse(i);
        } catch (o) {
          throw new Jr(`Invalid token specified: invalid json for part #${n + 1} (${o.message})`);
        }
      })(t);
    } catch (e) {
      throw de.error("JwtUtils.decode", e), e;
    }
  }
  static async generateSignedJwt(t, e, r) {
    const n = `${Ue.encodeBase64Url(new TextEncoder().encode(JSON.stringify(t)))}.${Ue.encodeBase64Url(new TextEncoder().encode(JSON.stringify(e)))}`, s = await window.crypto.subtle.sign({ name: "ECDSA", hash: { name: "SHA-256" } }, r, new TextEncoder().encode(n));
    return `${n}.${Ue.encodeBase64Url(new Uint8Array(s))}`;
  }
  static async generateSignedJwtWithHmac(t, e, r) {
    const n = `${Ue.encodeBase64Url(new TextEncoder().encode(JSON.stringify(t)))}.${Ue.encodeBase64Url(new TextEncoder().encode(JSON.stringify(e)))}`, s = await window.crypto.subtle.sign("HMAC", r, new TextEncoder().encode(n));
    return `${n}.${Ue.encodeBase64Url(new Uint8Array(s))}`;
  }
}, eo = (t) => btoa([...new Uint8Array(t)].map((e) => String.fromCharCode(e)).join("")), Jd = class gt {
  static _randomWord() {
    const e = new Uint32Array(1);
    return crypto.getRandomValues(e), e[0];
  }
  static generateUUIDv4() {
    return "10000000-1000-4000-8000-100000000000".replace(/[018]/g, (r) => (+r ^ gt._randomWord() & 15 >> +r / 4).toString(16)).replace(/-/g, "");
  }
  static generateCodeVerifier() {
    return gt.generateUUIDv4() + gt.generateUUIDv4() + gt.generateUUIDv4();
  }
  static async generateCodeChallenge(e) {
    if (!crypto.subtle) throw new Error("Crypto.subtle is available only in secure contexts (HTTPS).");
    try {
      const r = new TextEncoder().encode(e), n = await crypto.subtle.digest("SHA-256", r);
      return eo(n).replace(/\+/g, "-").replace(/\//g, "_").replace(/=+$/, "");
    } catch (r) {
      throw de.error("CryptoUtils.generateCodeChallenge", r), r;
    }
  }
  static generateBasicAuth(e, r) {
    const n = new TextEncoder().encode([e, r].join(":"));
    return eo(n);
  }
  static async hash(e, r) {
    const n = new TextEncoder().encode(r), s = await crypto.subtle.digest(e, n);
    return new Uint8Array(s);
  }
  static async customCalculateJwkThumbprint(e) {
    let r;
    switch (e.kty) {
      case "RSA":
        r = { e: e.e, kty: e.kty, n: e.n };
        break;
      case "EC":
        r = { crv: e.crv, kty: e.kty, x: e.x, y: e.y };
        break;
      case "OKP":
        r = { crv: e.crv, kty: e.kty, x: e.x };
        break;
      case "oct":
        r = { crv: e.k, kty: e.kty };
        break;
      default:
        throw new Error("Unknown jwk type");
    }
    const n = await gt.hash("SHA-256", JSON.stringify(r));
    return gt.encodeBase64Url(n);
  }
  static async generateDPoPProof({ url: e, accessToken: r, httpMethod: n, keyPair: s, nonce: i }) {
    let o, a;
    const c = { jti: window.crypto.randomUUID(), htm: n ?? "GET", htu: e, iat: Math.floor(Date.now() / 1e3) };
    r && (o = await gt.hash("SHA-256", r), a = gt.encodeBase64Url(o), c.ath = a), i && (c.nonce = i);
    try {
      const u = await crypto.subtle.exportKey("jwk", s.publicKey), l = { alg: "ES256", typ: "dpop+jwt", jwk: { crv: u.crv, kty: u.kty, x: u.x, y: u.y } };
      return await nn.generateSignedJwt(l, c, s.privateKey);
    } catch (u) {
      throw u instanceof TypeError ? new Error(`Error exporting dpop public key: ${u.message}`) : u;
    }
  }
  static async generateDPoPJkt(e) {
    try {
      const r = await crypto.subtle.exportKey("jwk", e.publicKey);
      return await gt.customCalculateJwkThumbprint(r);
    } catch (r) {
      throw r instanceof TypeError ? new Error(`Could not retrieve dpop keys from storage: ${r.message}`) : r;
    }
  }
  static async generateDPoPKeys() {
    return await window.crypto.subtle.generateKey({ name: "ECDSA", namedCurve: "P-256" }, !1, ["sign", "verify"]);
  }
  static async generateClientAssertionJwt(e, r, n, s = "HS256") {
    const i = Math.floor(Date.now() / 1e3), o = { alg: s, typ: "JWT" }, a = { iss: e, sub: e, aud: n, jti: gt.generateUUIDv4(), exp: i + 300, iat: i }, c = { HS256: "SHA-256", HS384: "SHA-384", HS512: "SHA-512" }[s];
    if (!c) throw new Error(`Unsupported algorithm: ${s}. Supported algorithms are: HS256, HS384, HS512`);
    const u = new TextEncoder(), l = await crypto.subtle.importKey("raw", u.encode(r), { name: "HMAC", hash: c }, !1, ["sign"]);
    return await nn.generateSignedJwtWithHmac(o, a, l);
  }
};
Jd.encodeBase64Url = (t) => eo(t).replace(/=/g, "").replace(/\+/g, "-").replace(/\//g, "_");
var Ue = Jd, qt = class {
  constructor(t) {
    this._name = t, this._callbacks = [], this._logger = new de(`Event('${this._name}')`);
  }
  addHandler(t) {
    return this._callbacks.push(t), () => this.removeHandler(t);
  }
  removeHandler(t) {
    const e = this._callbacks.lastIndexOf(t);
    e >= 0 && this._callbacks.splice(e, 1);
  }
  async raise(...t) {
    this._logger.debug("raise:", ...t);
    for (const e of this._callbacks) await e(...t);
  }
}, Xa = class {
  static center({ ...t }) {
    var e;
    return t.width == null && (t.width = (e = [800, 720, 600, 480].find((r) => r <= window.outerWidth / 1.618)) != null ? e : 360), t.left != null || (t.left = Math.max(0, Math.round(window.screenX + (window.outerWidth - t.width) / 2))), t.height != null && (t.top != null || (t.top = Math.max(0, Math.round(window.screenY + (window.outerHeight - t.height) / 2)))), t;
  }
  static serialize(t) {
    return Object.entries(t).filter(([, e]) => e != null).map(([e, r]) => `${e}=${typeof r != "boolean" ? r : r ? "yes" : "no"}`).join(",");
  }
}, Ot = class gs extends qt {
  constructor() {
    super(...arguments), this._logger = new de(`Timer('${this._name}')`), this._timerHandle = null, this._expiration = 0, this._callback = () => {
      const e = this._expiration - gs.getEpochTime();
      this._logger.debug("timer completes in", e), this._expiration <= gs.getEpochTime() && (this.cancel(), super.raise());
    };
  }
  static getEpochTime() {
    return Math.floor(Date.now() / 1e3);
  }
  init(e) {
    const r = this._logger.create("init");
    e = Math.max(Math.floor(e), 1);
    const n = gs.getEpochTime() + e;
    if (this.expiration === n && this._timerHandle) return void r.debug("skipping since already initialized for expiration at", this.expiration);
    this.cancel(), r.debug("using duration", e), this._expiration = n;
    const s = Math.min(e, 5);
    this._timerHandle = setInterval(this._callback, 1e3 * s);
  }
  get expiration() {
    return this._expiration;
  }
  cancel() {
    this._logger.create("cancel"), this._timerHandle && (clearInterval(this._timerHandle), this._timerHandle = null);
  }
}, to = class {
  static readParams(t, e = "query") {
    if (!t) throw new TypeError("Invalid URL");
    const r = new URL(t, "http://127.0.0.1")[e === "fragment" ? "hash" : "search"];
    return new URLSearchParams(r.slice(1));
  }
}, Ir = ";", nr = class extends Error {
  constructor(t, e) {
    var r, n, s;
    if (super(t.error_description || t.error || ""), this.form = e, this.name = "ErrorResponse", !t.error) throw de.error("ErrorResponse", "No error passed"), new Error("No error passed");
    this.error = t.error, this.error_description = (r = t.error_description) != null ? r : null, this.error_uri = (n = t.error_uri) != null ? n : null, this.state = t.userState, this.session_state = (s = t.session_state) != null ? s : null, this.url_state = t.url_state;
  }
}, la = class extends Error {
  constructor(t) {
    super(t), this.name = "ErrorTimeout";
  }
}, bw = class {
  constructor(t) {
    this._logger = new de("AccessTokenEvents"), this._expiringTimer = new Ot("Access token expiring"), this._expiredTimer = new Ot("Access token expired"), this._expiringNotificationTimeInSeconds = t.expiringNotificationTimeInSeconds;
  }
  async load(t) {
    const e = this._logger.create("load");
    if (t.access_token && t.expires_in !== void 0) {
      const r = t.expires_in;
      if (e.debug("access token present, remaining duration:", r), r > 0) {
        let s = r - this._expiringNotificationTimeInSeconds;
        s <= 0 && (s = 1), e.debug("registering expiring timer, raising in", s, "seconds"), this._expiringTimer.init(s);
      } else e.debug("canceling existing expiring timer because we're past expiration."), this._expiringTimer.cancel();
      const n = r + 1;
      e.debug("registering expired timer, raising in", n, "seconds"), this._expiredTimer.init(n);
    } else this._expiringTimer.cancel(), this._expiredTimer.cancel();
  }
  async unload() {
    this._logger.debug("unload: canceling existing access token timers"), this._expiringTimer.cancel(), this._expiredTimer.cancel();
  }
  addAccessTokenExpiring(t) {
    return this._expiringTimer.addHandler(t);
  }
  removeAccessTokenExpiring(t) {
    this._expiringTimer.removeHandler(t);
  }
  addAccessTokenExpired(t) {
    return this._expiredTimer.addHandler(t);
  }
  removeAccessTokenExpired(t) {
    this._expiredTimer.removeHandler(t);
  }
}, Sw = class {
  constructor(t, e, r, n, s) {
    this._callback = t, this._client_id = e, this._intervalInSeconds = n, this._stopOnError = s, this._logger = new de("CheckSessionIFrame"), this._timer = null, this._session_state = null, this._message = (o) => {
      o.origin === this._frame_origin && o.source === this._frame.contentWindow && (o.data === "error" ? (this._logger.error("error message from check session op iframe"), this._stopOnError && this.stop()) : o.data === "changed" ? (this._logger.debug("changed message from check session op iframe"), this.stop(), this._callback()) : this._logger.debug(o.data + " message from check session op iframe"));
    };
    const i = new URL(r);
    this._frame_origin = i.origin, this._frame = window.document.createElement("iframe"), this._frame.style.visibility = "hidden", this._frame.style.position = "fixed", this._frame.style.left = "-1000px", this._frame.style.top = "0", this._frame.width = "0", this._frame.height = "0", this._frame.src = i.href;
  }
  load() {
    return new Promise((t) => {
      this._frame.onload = () => {
        t();
      }, window.document.body.appendChild(this._frame), window.addEventListener("message", this._message, !1);
    });
  }
  start(t) {
    if (this._session_state === t) return;
    this._logger.create("start"), this.stop(), this._session_state = t;
    const e = () => {
      this._frame.contentWindow && this._session_state && this._frame.contentWindow.postMessage(this._client_id + " " + this._session_state, this._frame_origin);
    };
    e(), this._timer = setInterval(e, 1e3 * this._intervalInSeconds);
  }
  stop() {
    this._logger.create("stop"), this._session_state = null, this._timer && (clearInterval(this._timer), this._timer = null);
  }
}, Kd = class {
  constructor() {
    this._logger = new de("InMemoryWebStorage"), this._data = {};
  }
  clear() {
    this._logger.create("clear"), this._data = {};
  }
  getItem(t) {
    return this._logger.create(`getItem('${t}')`), this._data[t];
  }
  setItem(t, e) {
    this._logger.create(`setItem('${t}')`), this._data[t] = e;
  }
  removeItem(t) {
    this._logger.create(`removeItem('${t}')`), delete this._data[t];
  }
  get length() {
    return Object.getOwnPropertyNames(this._data).length;
  }
  key(t) {
    return Object.getOwnPropertyNames(this._data)[t];
  }
}, ro = class extends Error {
  constructor(t, e) {
    super(e), this.name = "ErrorDPoPNonce", this.nonce = t;
  }
}, da = class {
  constructor(t = [], e = null, r = {}) {
    this._jwtHandler = e, this._extraHeaders = r, this._logger = new de("JsonService"), this._contentTypes = [], this._contentTypes.push(...t, "application/json"), e && this._contentTypes.push("application/jwt");
  }
  async fetchWithTimeout(t, e = {}) {
    const { timeoutInSeconds: r, ...n } = e;
    if (!r) return await fetch(t, n);
    const s = new AbortController(), i = setTimeout(() => s.abort(), 1e3 * r);
    try {
      return await fetch(t, { ...e, signal: s.signal });
    } catch (o) {
      throw o instanceof DOMException && o.name === "AbortError" ? new la("Network timed out") : o;
    } finally {
      clearTimeout(i);
    }
  }
  async getJson(t, { token: e, credentials: r, timeoutInSeconds: n } = {}) {
    const s = this._logger.create("getJson"), i = { Accept: this._contentTypes.join(", ") };
    let o;
    e && (s.debug("token passed, setting Authorization header"), i.Authorization = "Bearer " + e), this._appendExtraHeaders(i);
    try {
      s.debug("url:", t), o = await this.fetchWithTimeout(t, { method: "GET", headers: i, timeoutInSeconds: n, credentials: r });
    } catch (u) {
      throw s.error("Network Error"), u;
    }
    s.debug("HTTP response received, status", o.status);
    const a = o.headers.get("Content-Type");
    if (a && !this._contentTypes.find((u) => a.startsWith(u)) && s.throw(new Error(`Invalid response Content-Type: ${a ?? "undefined"}, from URL: ${t}`)), o.ok && this._jwtHandler && a?.startsWith("application/jwt")) return await this._jwtHandler(await o.text());
    let c;
    try {
      c = await o.json();
    } catch (u) {
      throw s.error("Error parsing JSON response", u), o.ok ? u : new Error(`${o.statusText} (${o.status})`);
    }
    if (!o.ok)
      throw s.error("Error from server:", c), c.error ? new nr(c) : new Error(`${o.statusText} (${o.status}): ${JSON.stringify(c)}`);
    return c;
  }
  async postForm(t, { body: e, basicAuth: r, timeoutInSeconds: n, initCredentials: s, extraHeaders: i }) {
    const o = this._logger.create("postForm"), a = { Accept: this._contentTypes.join(", "), "Content-Type": "application/x-www-form-urlencoded", ...i };
    let c;
    r !== void 0 && (a.Authorization = "Basic " + r), this._appendExtraHeaders(a);
    try {
      o.debug("url:", t), c = await this.fetchWithTimeout(t, { method: "POST", headers: a, body: e, timeoutInSeconds: n, credentials: s });
    } catch (p) {
      throw o.error("Network error"), p;
    }
    o.debug("HTTP response received, status", c.status);
    const u = c.headers.get("Content-Type");
    if (u && !this._contentTypes.find((p) => u.startsWith(p))) throw new Error(`Invalid response Content-Type: ${u ?? "undefined"}, from URL: ${t}`);
    const l = await c.text();
    let h = {};
    if (l) try {
      h = JSON.parse(l);
    } catch (p) {
      throw o.error("Error parsing JSON response", p), c.ok ? p : new Error(`${c.statusText} (${c.status})`);
    }
    if (!c.ok) {
      if (o.error("Error from server:", h), c.headers.has("dpop-nonce")) {
        const p = c.headers.get("dpop-nonce");
        throw new ro(p, `${JSON.stringify(h)}`);
      }
      throw h.error ? new nr(h, e) : new Error(`${c.statusText} (${c.status}): ${JSON.stringify(h)}`);
    }
    return h;
  }
  _appendExtraHeaders(t) {
    const e = this._logger.create("appendExtraHeaders"), r = Object.keys(this._extraHeaders), n = ["accept", "content-type"], s = ["authorization"];
    r.length !== 0 && r.forEach((i) => {
      if (n.includes(i.toLocaleLowerCase())) return void e.warn("Protected header could not be set", i, n);
      if (s.includes(i.toLocaleLowerCase()) && Object.keys(t).includes(i)) return void e.warn("Header could not be overridden", i, s);
      const o = typeof this._extraHeaders[i] == "function" ? this._extraHeaders[i]() : this._extraHeaders[i];
      o && o !== "" && (t[i] = o);
    });
  }
}, kw = class {
  constructor(t) {
    this._settings = t, this._logger = new de("MetadataService"), this._signingKeys = null, this._metadata = null, this._metadataUrl = this._settings.metadataUrl, this._jsonService = new da(["application/jwk-set+json"], null, this._settings.extraHeaders), this._settings.signingKeys && (this._logger.debug("using signingKeys from settings"), this._signingKeys = this._settings.signingKeys), this._settings.metadata && (this._logger.debug("using metadata from settings"), this._metadata = this._settings.metadata), this._settings.fetchRequestCredentials && (this._logger.debug("using fetchRequestCredentials from settings"), this._fetchRequestCredentials = this._settings.fetchRequestCredentials);
  }
  resetSigningKeys() {
    this._signingKeys = null;
  }
  async getMetadata() {
    const t = this._logger.create("getMetadata");
    if (this._metadata) return t.debug("using cached values"), this._metadata;
    if (!this._metadataUrl) throw t.throw(new Error("No authority or metadataUrl configured on settings")), null;
    t.debug("getting metadata from", this._metadataUrl);
    const e = await this._jsonService.getJson(this._metadataUrl, { credentials: this._fetchRequestCredentials, timeoutInSeconds: this._settings.requestTimeoutInSeconds });
    return t.debug("merging remote JSON with seed metadata"), this._metadata = Object.assign({}, e, this._settings.metadataSeed), this._metadata;
  }
  getIssuer() {
    return this._getMetadataProperty("issuer");
  }
  getAuthorizationEndpoint() {
    return this._getMetadataProperty("authorization_endpoint");
  }
  getUserInfoEndpoint() {
    return this._getMetadataProperty("userinfo_endpoint");
  }
  getTokenEndpoint(t = !0) {
    return this._getMetadataProperty("token_endpoint", t);
  }
  getCheckSessionIframe() {
    return this._getMetadataProperty("check_session_iframe", !0);
  }
  getEndSessionEndpoint() {
    return this._getMetadataProperty("end_session_endpoint", !0);
  }
  getRevocationEndpoint(t = !0) {
    return this._getMetadataProperty("revocation_endpoint", t);
  }
  getKeysEndpoint(t = !0) {
    return this._getMetadataProperty("jwks_uri", t);
  }
  async _getMetadataProperty(t, e = !1) {
    const r = this._logger.create(`_getMetadataProperty('${t}')`), n = await this.getMetadata();
    if (r.debug("resolved"), n[t] === void 0) {
      if (e === !0) return void r.warn("Metadata does not contain optional property");
      r.throw(new Error("Metadata does not contain property " + t));
    }
    return n[t];
  }
  async getSigningKeys() {
    const t = this._logger.create("getSigningKeys");
    if (this._signingKeys) return t.debug("returning signingKeys from cache"), this._signingKeys;
    const e = await this.getKeysEndpoint(!1);
    t.debug("got jwks_uri", e);
    const r = await this._jsonService.getJson(e, { timeoutInSeconds: this._settings.requestTimeoutInSeconds });
    if (t.debug("got key set", r), !Array.isArray(r.keys)) throw t.throw(new Error("Missing keys on keyset")), null;
    return this._signingKeys = r.keys, this._signingKeys;
  }
}, ha = class {
  constructor({ prefix: t = "oidc.", store: e = localStorage } = {}) {
    this._logger = new de("WebStorageStateStore"), this._store = e, this._prefix = t;
  }
  async set(t, e) {
    this._logger.create(`set('${t}')`), t = this._prefix + t, await this._store.setItem(t, e);
  }
  async get(t) {
    return this._logger.create(`get('${t}')`), t = this._prefix + t, await this._store.getItem(t);
  }
  async remove(t) {
    this._logger.create(`remove('${t}')`), t = this._prefix + t;
    const e = await this._store.getItem(t);
    return await this._store.removeItem(t), e;
  }
  async getAllKeys() {
    this._logger.create("getAllKeys");
    const t = await this._store.length, e = [];
    for (let r = 0; r < t; r++) {
      const n = await this._store.key(r);
      n && n.indexOf(this._prefix) === 0 && e.push(n.substr(this._prefix.length));
    }
    return e;
  }
}, no = class {
  constructor({ authority: t, metadataUrl: e, metadata: r, signingKeys: n, metadataSeed: s, client_id: i, client_secret: o, response_type: a = "code", scope: c = "openid", redirect_uri: u, post_logout_redirect_uri: l, client_authentication: h = "client_secret_post", token_endpoint_auth_signing_alg: p = "HS256", prompt: g, display: S, max_age: k, ui_locales: y, acr_values: b, resource: m, response_mode: w, filterProtocolClaims: $ = !0, loadUserInfo: d = !1, requestTimeoutInSeconds: f, staleStateAgeInSeconds: _ = 900, mergeClaimsStrategy: E = { array: "replace" }, disablePKCE: z = !1, stateStore: C, revokeTokenAdditionalContentTypes: P, fetchRequestCredentials: j, refreshTokenAllowedScope: O, extraQueryParams: q = {}, extraTokenParams: se = {}, extraHeaders: Ee = {}, dpop: be, omitScopeWhenRequesting: oe = !1 }) {
    var Me;
    if (this.authority = t, e ? this.metadataUrl = e : (this.metadataUrl = t, t && (this.metadataUrl.endsWith("/") || (this.metadataUrl += "/"), this.metadataUrl += ".well-known/openid-configuration")), this.metadata = r, this.metadataSeed = s, this.signingKeys = n, this.client_id = i, this.client_secret = o, this.response_type = a, this.scope = c, this.redirect_uri = u, this.post_logout_redirect_uri = l, this.client_authentication = h, this.token_endpoint_auth_signing_alg = p, this.prompt = g, this.display = S, this.max_age = k, this.ui_locales = y, this.acr_values = b, this.resource = m, this.response_mode = w, this.filterProtocolClaims = $ == null || $, this.loadUserInfo = !!d, this.staleStateAgeInSeconds = _, this.mergeClaimsStrategy = E, this.omitScopeWhenRequesting = oe, this.disablePKCE = !!z, this.revokeTokenAdditionalContentTypes = P, this.fetchRequestCredentials = j || "same-origin", this.requestTimeoutInSeconds = f, C) this.stateStore = C;
    else {
      const Z = typeof window < "u" ? window.localStorage : new Kd();
      this.stateStore = new ha({ store: Z });
    }
    if (this.refreshTokenAllowedScope = O, this.extraQueryParams = q, this.extraTokenParams = se, this.extraHeaders = Ee, this.dpop = be, this.dpop && !((Me = this.dpop) != null && Me.store)) throw new Error("A DPoPStore is required when dpop is enabled");
  }
}, $w = class {
  constructor(t, e) {
    this._settings = t, this._metadataService = e, this._logger = new de("UserInfoService"), this._getClaimsFromJwt = async (r) => {
      const n = this._logger.create("_getClaimsFromJwt");
      try {
        const s = nn.decode(r);
        return n.debug("JWT decoding successful"), s;
      } catch (s) {
        throw n.error("Error parsing JWT response"), s;
      }
    }, this._jsonService = new da(void 0, this._getClaimsFromJwt, this._settings.extraHeaders);
  }
  async getClaims(t) {
    const e = this._logger.create("getClaims");
    t || this._logger.throw(new Error("No token passed"));
    const r = await this._metadataService.getUserInfoEndpoint();
    e.debug("got userinfo url", r);
    const n = await this._jsonService.getJson(r, { token: t, credentials: this._settings.fetchRequestCredentials, timeoutInSeconds: this._settings.requestTimeoutInSeconds });
    return e.debug("got claims", n), n;
  }
}, Qd = class {
  constructor(t, e) {
    this._settings = t, this._metadataService = e, this._logger = new de("TokenClient"), this._jsonService = new da(this._settings.revokeTokenAdditionalContentTypes, null, this._settings.extraHeaders);
  }
  async exchangeCode({ grant_type: t = "authorization_code", redirect_uri: e = this._settings.redirect_uri, client_id: r = this._settings.client_id, client_secret: n = this._settings.client_secret, extraHeaders: s, ...i }) {
    const o = this._logger.create("exchangeCode");
    r || o.throw(new Error("A client_id is required")), e || o.throw(new Error("A redirect_uri is required")), i.code || o.throw(new Error("A code is required"));
    const a = new URLSearchParams({ grant_type: t, redirect_uri: e });
    for (const [h, p] of Object.entries(i)) p != null && a.set(h, p);
    if ((this._settings.client_authentication === "client_secret_basic" || this._settings.client_authentication === "client_secret_jwt") && n == null) throw o.throw(new Error("A client_secret is required")), null;
    let c;
    const u = await this._metadataService.getTokenEndpoint(!1);
    switch (this._settings.client_authentication) {
      case "client_secret_basic":
        c = Ue.generateBasicAuth(r, n);
        break;
      case "client_secret_post":
        a.append("client_id", r), n && a.append("client_secret", n);
        break;
      case "client_secret_jwt": {
        const h = await Ue.generateClientAssertionJwt(r, n, u, this._settings.token_endpoint_auth_signing_alg);
        a.append("client_id", r), a.append("client_assertion_type", "urn:ietf:params:oauth:client-assertion-type:jwt-bearer"), a.append("client_assertion", h);
        break;
      }
    }
    o.debug("got token endpoint");
    const l = await this._jsonService.postForm(u, { body: a, basicAuth: c, timeoutInSeconds: this._settings.requestTimeoutInSeconds, initCredentials: this._settings.fetchRequestCredentials, extraHeaders: s });
    return o.debug("got response"), l;
  }
  async exchangeCredentials({ grant_type: t = "password", client_id: e = this._settings.client_id, client_secret: r = this._settings.client_secret, scope: n = this._settings.scope, ...s }) {
    const i = this._logger.create("exchangeCredentials");
    e || i.throw(new Error("A client_id is required"));
    const o = new URLSearchParams({ grant_type: t });
    this._settings.omitScopeWhenRequesting || o.set("scope", n);
    for (const [l, h] of Object.entries(s)) h != null && o.set(l, h);
    if ((this._settings.client_authentication === "client_secret_basic" || this._settings.client_authentication === "client_secret_jwt") && r == null) throw i.throw(new Error("A client_secret is required")), null;
    let a;
    const c = await this._metadataService.getTokenEndpoint(!1);
    switch (this._settings.client_authentication) {
      case "client_secret_basic":
        a = Ue.generateBasicAuth(e, r);
        break;
      case "client_secret_post":
        o.append("client_id", e), r && o.append("client_secret", r);
        break;
      case "client_secret_jwt": {
        const l = await Ue.generateClientAssertionJwt(e, r, c, this._settings.token_endpoint_auth_signing_alg);
        o.append("client_id", e), o.append("client_assertion_type", "urn:ietf:params:oauth:client-assertion-type:jwt-bearer"), o.append("client_assertion", l);
        break;
      }
    }
    i.debug("got token endpoint");
    const u = await this._jsonService.postForm(c, { body: o, basicAuth: a, timeoutInSeconds: this._settings.requestTimeoutInSeconds, initCredentials: this._settings.fetchRequestCredentials });
    return i.debug("got response"), u;
  }
  async exchangeRefreshToken({ grant_type: t = "refresh_token", client_id: e = this._settings.client_id, client_secret: r = this._settings.client_secret, timeoutInSeconds: n, extraHeaders: s, ...i }) {
    const o = this._logger.create("exchangeRefreshToken");
    e || o.throw(new Error("A client_id is required")), i.refresh_token || o.throw(new Error("A refresh_token is required"));
    const a = new URLSearchParams({ grant_type: t });
    for (const [h, p] of Object.entries(i)) Array.isArray(p) ? p.forEach((g) => a.append(h, g)) : p != null && a.set(h, p);
    if ((this._settings.client_authentication === "client_secret_basic" || this._settings.client_authentication === "client_secret_jwt") && r == null) throw o.throw(new Error("A client_secret is required")), null;
    let c;
    const u = await this._metadataService.getTokenEndpoint(!1);
    switch (this._settings.client_authentication) {
      case "client_secret_basic":
        c = Ue.generateBasicAuth(e, r);
        break;
      case "client_secret_post":
        a.append("client_id", e), r && a.append("client_secret", r);
        break;
      case "client_secret_jwt": {
        const h = await Ue.generateClientAssertionJwt(e, r, u, this._settings.token_endpoint_auth_signing_alg);
        a.append("client_id", e), a.append("client_assertion_type", "urn:ietf:params:oauth:client-assertion-type:jwt-bearer"), a.append("client_assertion", h);
        break;
      }
    }
    o.debug("got token endpoint");
    const l = await this._jsonService.postForm(u, { body: a, basicAuth: c, timeoutInSeconds: n, initCredentials: this._settings.fetchRequestCredentials, extraHeaders: s });
    return o.debug("got response"), l;
  }
  async revoke(t) {
    var e;
    const r = this._logger.create("revoke");
    t.token || r.throw(new Error("A token is required"));
    const n = await this._metadataService.getRevocationEndpoint(!1);
    r.debug(`got revocation endpoint, revoking ${(e = t.token_type_hint) != null ? e : "default token type"}`);
    const s = new URLSearchParams();
    for (const [i, o] of Object.entries(t)) o != null && s.set(i, o);
    s.set("client_id", this._settings.client_id), this._settings.client_secret && s.set("client_secret", this._settings.client_secret), await this._jsonService.postForm(n, { body: s, timeoutInSeconds: this._settings.requestTimeoutInSeconds }), r.debug("got response");
  }
}, Ew = class {
  constructor(t, e, r) {
    this._settings = t, this._metadataService = e, this._claimsService = r, this._logger = new de("ResponseValidator"), this._userInfoService = new $w(this._settings, this._metadataService), this._tokenClient = new Qd(this._settings, this._metadataService);
  }
  async validateSigninResponse(t, e, r) {
    const n = this._logger.create("validateSigninResponse");
    this._processSigninState(t, e), n.debug("state processed"), await this._processCode(t, e, r), n.debug("code processed"), t.isOpenId && this._validateIdTokenAttributes(t), n.debug("tokens validated"), await this._processClaims(t, e?.skipUserInfo, t.isOpenId), n.debug("claims processed");
  }
  async validateCredentialsResponse(t, e) {
    const r = this._logger.create("validateCredentialsResponse"), n = t.isOpenId && !!t.id_token;
    n && this._validateIdTokenAttributes(t), r.debug("tokens validated"), await this._processClaims(t, e, n), r.debug("claims processed");
  }
  async validateRefreshResponse(t, e) {
    const r = this._logger.create("validateRefreshResponse");
    t.userState = e.data, t.session_state != null || (t.session_state = e.session_state), t.scope != null || (t.scope = e.scope), t.isOpenId && t.id_token && (this._validateIdTokenAttributes(t, e.id_token), r.debug("ID Token validated")), t.id_token || (t.id_token = e.id_token, t.profile = e.profile);
    const n = t.isOpenId && !!t.id_token;
    await this._processClaims(t, !1, n), r.debug("claims processed");
  }
  validateSignoutResponse(t, e) {
    const r = this._logger.create("validateSignoutResponse");
    if (e.id !== t.state && r.throw(new Error("State does not match")), r.debug("state validated"), t.userState = e.data, t.error) throw r.warn("Response was error", t.error), new nr(t);
  }
  _processSigninState(t, e) {
    const r = this._logger.create("_processSigninState");
    if (e.id !== t.state && r.throw(new Error("State does not match")), e.client_id || r.throw(new Error("No client_id on state")), e.authority || r.throw(new Error("No authority on state")), this._settings.authority !== e.authority && r.throw(new Error("authority mismatch on settings vs. signin state")), this._settings.client_id && this._settings.client_id !== e.client_id && r.throw(new Error("client_id mismatch on settings vs. signin state")), r.debug("state validated"), t.userState = e.data, t.url_state = e.url_state, t.scope != null || (t.scope = e.scope), t.error) throw r.warn("Response was error", t.error), new nr(t);
    e.code_verifier && !t.code && r.throw(new Error("Expected code in response"));
  }
  async _processClaims(t, e = !1, r = !0) {
    const n = this._logger.create("_processClaims");
    if (t.profile = this._claimsService.filterProtocolClaims(t.profile), e || !this._settings.loadUserInfo || !t.access_token) return void n.debug("not loading user info");
    n.debug("loading user info");
    const s = await this._userInfoService.getClaims(t.access_token);
    n.debug("user info claims received from user info endpoint"), r && s.sub !== t.profile.sub && n.throw(new Error("subject from UserInfo response does not match subject in ID Token")), t.profile = this._claimsService.mergeClaims(t.profile, this._claimsService.filterProtocolClaims(s)), n.debug("user info claims received, updated profile:", t.profile);
  }
  async _processCode(t, e, r) {
    const n = this._logger.create("_processCode");
    if (t.code) {
      n.debug("Validating code");
      const s = await this._tokenClient.exchangeCode({ client_id: e.client_id, client_secret: e.client_secret, code: t.code, redirect_uri: e.redirect_uri, code_verifier: e.code_verifier, extraHeaders: r, ...e.extraTokenParams });
      Object.assign(t, s);
    } else n.debug("No code to process");
  }
  _validateIdTokenAttributes(t, e) {
    var r;
    const n = this._logger.create("_validateIdTokenAttributes");
    n.debug("decoding ID Token JWT");
    const s = nn.decode((r = t.id_token) != null ? r : "");
    if (s.sub || n.throw(new Error("ID Token is missing a subject claim")), e) {
      const i = nn.decode(e);
      s.sub !== i.sub && n.throw(new Error("sub in id_token does not match current sub")), s.auth_time && s.auth_time !== i.auth_time && n.throw(new Error("auth_time in id_token does not match original auth_time")), s.azp && s.azp !== i.azp && n.throw(new Error("azp in id_token does not match original azp")), !s.azp && i.azp && n.throw(new Error("azp not in id_token, but present in original id_token"));
    }
    t.profile = s;
  }
}, Cs = class so {
  constructor(e) {
    this.id = e.id || Ue.generateUUIDv4(), this.data = e.data, e.created && e.created > 0 ? this.created = e.created : this.created = Ot.getEpochTime(), this.request_type = e.request_type, this.url_state = e.url_state;
  }
  toStorageString() {
    return new de("State").create("toStorageString"), JSON.stringify({ id: this.id, data: this.data, created: this.created, request_type: this.request_type, url_state: this.url_state });
  }
  static fromStorageString(e) {
    return de.createStatic("State", "fromStorageString"), Promise.resolve(new so(JSON.parse(e)));
  }
  static async clearStaleState(e, r) {
    const n = de.createStatic("State", "clearStaleState"), s = Ot.getEpochTime() - r, i = await e.getAllKeys();
    n.debug("got keys", i);
    for (let o = 0; o < i.length; o++) {
      const a = i[o], c = await e.get(a);
      let u = !1;
      if (c) try {
        const l = await so.fromStorageString(c);
        n.debug("got item from key:", a, l.created), l.created <= s && (u = !0);
      } catch (l) {
        n.error("Error parsing state for key:", a, l), u = !0;
      }
      else n.debug("no item in storage for key:", a), u = !0;
      u && (n.debug("removed item for key:", a), e.remove(a));
    }
  }
}, Yd = class io extends Cs {
  constructor(e) {
    super(e), this.code_verifier = e.code_verifier, this.code_challenge = e.code_challenge, this.authority = e.authority, this.client_id = e.client_id, this.redirect_uri = e.redirect_uri, this.scope = e.scope, this.client_secret = e.client_secret, this.extraTokenParams = e.extraTokenParams, this.response_mode = e.response_mode, this.skipUserInfo = e.skipUserInfo;
  }
  static async create(e) {
    const r = e.code_verifier === !0 ? Ue.generateCodeVerifier() : e.code_verifier || void 0, n = r ? await Ue.generateCodeChallenge(r) : void 0;
    return new io({ ...e, code_verifier: r, code_challenge: n });
  }
  toStorageString() {
    return new de("SigninState").create("toStorageString"), JSON.stringify({ id: this.id, data: this.data, created: this.created, request_type: this.request_type, url_state: this.url_state, code_verifier: this.code_verifier, authority: this.authority, client_id: this.client_id, redirect_uri: this.redirect_uri, scope: this.scope, client_secret: this.client_secret, extraTokenParams: this.extraTokenParams, response_mode: this.response_mode, skipUserInfo: this.skipUserInfo });
  }
  static fromStorageString(e) {
    de.createStatic("SigninState", "fromStorageString");
    const r = JSON.parse(e);
    return io.create(r);
  }
}, Xd = class eh {
  constructor(e) {
    this.url = e.url, this.state = e.state;
  }
  static async create({ url: e, authority: r, client_id: n, redirect_uri: s, response_type: i, scope: o, state_data: a, response_mode: c, request_type: u, client_secret: l, nonce: h, url_state: p, resource: g, skipUserInfo: S, extraQueryParams: k, extraTokenParams: y, disablePKCE: b, dpopJkt: m, omitScopeWhenRequesting: w, ...$ }) {
    if (!e) throw this._logger.error("create: No url passed"), new Error("url");
    if (!n) throw this._logger.error("create: No client_id passed"), new Error("client_id");
    if (!s) throw this._logger.error("create: No redirect_uri passed"), new Error("redirect_uri");
    if (!i) throw this._logger.error("create: No response_type passed"), new Error("response_type");
    if (!o) throw this._logger.error("create: No scope passed"), new Error("scope");
    if (!r) throw this._logger.error("create: No authority passed"), new Error("authority");
    const d = await Yd.create({ data: a, request_type: u, url_state: p, code_verifier: !b, client_id: n, authority: r, redirect_uri: s, response_mode: c, client_secret: l, scope: o, extraTokenParams: y, skipUserInfo: S }), f = new URL(e);
    f.searchParams.append("client_id", n), f.searchParams.append("redirect_uri", s), f.searchParams.append("response_type", i), w || f.searchParams.append("scope", o), h && f.searchParams.append("nonce", h), m && f.searchParams.append("dpop_jkt", m);
    let _ = d.id;
    p && (_ = `${_}${Ir}${p}`), f.searchParams.append("state", _), d.code_challenge && (f.searchParams.append("code_challenge", d.code_challenge), f.searchParams.append("code_challenge_method", "S256")), g && (Array.isArray(g) ? g : [g]).forEach((E) => f.searchParams.append("resource", E));
    for (const [E, z] of Object.entries({ response_mode: c, ...$, ...k })) z != null && f.searchParams.append(E, z.toString());
    return new eh({ url: f.href, state: d });
  }
};
Xd._logger = new de("SigninRequest");
var Tw = Xd, pi = class {
  constructor(t) {
    if (this.access_token = "", this.token_type = "", this.profile = {}, this.state = t.get("state"), this.session_state = t.get("session_state"), this.state) {
      const e = decodeURIComponent(this.state).split(Ir);
      this.state = e[0], e.length > 1 && (this.url_state = e.slice(1).join(Ir));
    }
    this.error = t.get("error"), this.error_description = t.get("error_description"), this.error_uri = t.get("error_uri"), this.code = t.get("code");
  }
  get expires_in() {
    if (this.expires_at !== void 0) return this.expires_at - Ot.getEpochTime();
  }
  set expires_in(t) {
    typeof t == "string" && (t = Number(t)), t !== void 0 && t >= 0 && (this.expires_at = Math.floor(t) + Ot.getEpochTime());
  }
  get isOpenId() {
    var t;
    return ((t = this.scope) == null ? void 0 : t.split(" ").includes("openid")) || !!this.id_token;
  }
}, Rw = class {
  constructor({ url: t, state_data: e, id_token_hint: r, post_logout_redirect_uri: n, extraQueryParams: s, request_type: i, client_id: o, url_state: a }) {
    if (this._logger = new de("SignoutRequest"), !t) throw this._logger.error("ctor: No url passed"), new Error("url");
    const c = new URL(t);
    if (r && c.searchParams.append("id_token_hint", r), o && c.searchParams.append("client_id", o), n && (c.searchParams.append("post_logout_redirect_uri", n), e || a)) {
      this.state = new Cs({ data: e, request_type: i, url_state: a });
      let u = this.state.id;
      a && (u = `${u}${Ir}${a}`), c.searchParams.append("state", u);
    }
    for (const [u, l] of Object.entries({ ...s })) l != null && c.searchParams.append(u, l.toString());
    this.url = c.href;
  }
}, Iw = class {
  constructor(t) {
    if (this.state = t.get("state"), this.state) {
      const e = decodeURIComponent(this.state).split(Ir);
      this.state = e[0], e.length > 1 && (this.url_state = e.slice(1).join(Ir));
    }
    this.error = t.get("error"), this.error_description = t.get("error_description"), this.error_uri = t.get("error_uri");
  }
}, Pw = ["nbf", "jti", "auth_time", "nonce", "acr", "amr", "azp", "at_hash"], Cw = ["sub", "iss", "aud", "exp", "iat"], Ow = class {
  constructor(t) {
    this._settings = t, this._logger = new de("ClaimsService");
  }
  filterProtocolClaims(t) {
    const e = { ...t };
    if (this._settings.filterProtocolClaims) {
      let r;
      r = Array.isArray(this._settings.filterProtocolClaims) ? this._settings.filterProtocolClaims : Pw;
      for (const n of r) Cw.includes(n) || delete e[n];
    }
    return e;
  }
  mergeClaims(t, e) {
    const r = { ...t };
    for (const [n, s] of Object.entries(e)) if (r[n] !== s) if (Array.isArray(r[n]) || Array.isArray(s)) if (this._settings.mergeClaimsStrategy.array == "replace") r[n] = s;
    else {
      const i = Array.isArray(r[n]) ? r[n] : [r[n]];
      for (const o of Array.isArray(s) ? s : [s]) i.includes(o) || i.push(o);
      r[n] = i;
    }
    else typeof r[n] == "object" && typeof s == "object" ? r[n] = this.mergeClaims(r[n], s) : r[n] = s;
    return r;
  }
}, th = class {
  constructor(t, e) {
    this.keys = t, this.nonce = e;
  }
}, Aw = class {
  constructor(t, e) {
    this._logger = new de("OidcClient"), this.settings = t instanceof no ? t : new no(t), this.metadataService = e ?? new kw(this.settings), this._claimsService = new Ow(this.settings), this._validator = new Ew(this.settings, this.metadataService, this._claimsService), this._tokenClient = new Qd(this.settings, this.metadataService);
  }
  async createSigninRequest({ state: t, request: e, request_uri: r, request_type: n, id_token_hint: s, login_hint: i, skipUserInfo: o, nonce: a, url_state: c, response_type: u = this.settings.response_type, scope: l = this.settings.scope, redirect_uri: h = this.settings.redirect_uri, prompt: p = this.settings.prompt, display: g = this.settings.display, max_age: S = this.settings.max_age, ui_locales: k = this.settings.ui_locales, acr_values: y = this.settings.acr_values, resource: b = this.settings.resource, response_mode: m = this.settings.response_mode, extraQueryParams: w = this.settings.extraQueryParams, extraTokenParams: $ = this.settings.extraTokenParams, dpopJkt: d, omitScopeWhenRequesting: f = this.settings.omitScopeWhenRequesting }) {
    const _ = this._logger.create("createSigninRequest");
    if (u !== "code") throw new Error("Only the Authorization Code flow (with PKCE) is supported");
    const E = await this.metadataService.getAuthorizationEndpoint();
    _.debug("Received authorization endpoint", E);
    const z = await Tw.create({ url: E, authority: this.settings.authority, client_id: this.settings.client_id, redirect_uri: h, response_type: u, scope: l, state_data: t, url_state: c, prompt: p, display: g, max_age: S, ui_locales: k, id_token_hint: s, login_hint: i, acr_values: y, dpopJkt: d, resource: b, request: e, request_uri: r, extraQueryParams: w, extraTokenParams: $, request_type: n, response_mode: m, client_secret: this.settings.client_secret, skipUserInfo: o, nonce: a, disablePKCE: this.settings.disablePKCE, omitScopeWhenRequesting: f });
    await this.clearStaleState();
    const C = z.state;
    return await this.settings.stateStore.set(C.id, C.toStorageString()), z;
  }
  async readSigninResponseState(t, e = !1) {
    const r = this._logger.create("readSigninResponseState"), n = new pi(to.readParams(t, this.settings.response_mode));
    if (!n.state) throw r.throw(new Error("No state in response")), null;
    const s = await this.settings.stateStore[e ? "remove" : "get"](n.state);
    if (!s) throw r.throw(new Error("No matching state found in storage")), null;
    return { state: await Yd.fromStorageString(s), response: n };
  }
  async processSigninResponse(t, e, r = !0) {
    const n = this._logger.create("processSigninResponse"), { state: s, response: i } = await this.readSigninResponseState(t, r);
    if (n.debug("received state from storage; validating response"), this.settings.dpop && this.settings.dpop.store) {
      const o = await this.getDpopProof(this.settings.dpop.store);
      e = { ...e, DPoP: o };
    }
    try {
      await this._validator.validateSigninResponse(i, s, e);
    } catch (o) {
      if (!(o instanceof ro && this.settings.dpop)) throw o;
      {
        const a = await this.getDpopProof(this.settings.dpop.store, o.nonce);
        e.DPoP = a, await this._validator.validateSigninResponse(i, s, e);
      }
    }
    return i;
  }
  async getDpopProof(t, e) {
    let r, n;
    return (await t.getAllKeys()).includes(this.settings.client_id) ? (n = await t.get(this.settings.client_id), n.nonce !== e && e && (n.nonce = e, await t.set(this.settings.client_id, n))) : (r = await Ue.generateDPoPKeys(), n = new th(r, e), await t.set(this.settings.client_id, n)), await Ue.generateDPoPProof({ url: await this.metadataService.getTokenEndpoint(!1), httpMethod: "POST", keyPair: n.keys, nonce: n.nonce });
  }
  async processResourceOwnerPasswordCredentials({ username: t, password: e, skipUserInfo: r = !1, extraTokenParams: n = {} }) {
    const s = await this._tokenClient.exchangeCredentials({ username: t, password: e, ...n }), i = new pi(new URLSearchParams());
    return Object.assign(i, s), await this._validator.validateCredentialsResponse(i, r), i;
  }
  async useRefreshToken({ state: t, redirect_uri: e, resource: r, timeoutInSeconds: n, extraHeaders: s, extraTokenParams: i }) {
    var o;
    const a = this._logger.create("useRefreshToken");
    let c, u;
    if (this.settings.refreshTokenAllowedScope === void 0) c = t.scope;
    else {
      const h = this.settings.refreshTokenAllowedScope.split(" ");
      c = (((o = t.scope) == null ? void 0 : o.split(" ")) || []).filter((p) => h.includes(p)).join(" ");
    }
    if (this.settings.dpop && this.settings.dpop.store) {
      const h = await this.getDpopProof(this.settings.dpop.store);
      s = { ...s, DPoP: h };
    }
    try {
      u = await this._tokenClient.exchangeRefreshToken({ refresh_token: t.refresh_token, scope: c, redirect_uri: e, resource: r, timeoutInSeconds: n, extraHeaders: s, ...i });
    } catch (h) {
      if (!(h instanceof ro && this.settings.dpop)) throw h;
      s.DPoP = await this.getDpopProof(this.settings.dpop.store, h.nonce), u = await this._tokenClient.exchangeRefreshToken({ refresh_token: t.refresh_token, scope: c, redirect_uri: e, resource: r, timeoutInSeconds: n, extraHeaders: s, ...i });
    }
    const l = new pi(new URLSearchParams());
    return Object.assign(l, u), a.debug("validating response", l), await this._validator.validateRefreshResponse(l, { ...t, scope: c }), l;
  }
  async createSignoutRequest({ state: t, id_token_hint: e, client_id: r, request_type: n, url_state: s, post_logout_redirect_uri: i = this.settings.post_logout_redirect_uri, extraQueryParams: o = this.settings.extraQueryParams } = {}) {
    const a = this._logger.create("createSignoutRequest"), c = await this.metadataService.getEndSessionEndpoint();
    if (!c) throw a.throw(new Error("No end session endpoint")), null;
    a.debug("Received end session endpoint", c), r || !i || e || (r = this.settings.client_id);
    const u = new Rw({ url: c, id_token_hint: e, client_id: r, post_logout_redirect_uri: i, state_data: t, extraQueryParams: o, request_type: n, url_state: s });
    await this.clearStaleState();
    const l = u.state;
    return l && (a.debug("Signout request has state to persist"), await this.settings.stateStore.set(l.id, l.toStorageString())), u;
  }
  async readSignoutResponseState(t, e = !1) {
    const r = this._logger.create("readSignoutResponseState"), n = new Iw(to.readParams(t, this.settings.response_mode));
    if (!n.state) {
      if (r.debug("No state in response"), n.error) throw r.warn("Response was error:", n.error), new nr(n);
      return { state: void 0, response: n };
    }
    const s = await this.settings.stateStore[e ? "remove" : "get"](n.state);
    if (!s) throw r.throw(new Error("No matching state found in storage")), null;
    return { state: await Cs.fromStorageString(s), response: n };
  }
  async processSignoutResponse(t) {
    const e = this._logger.create("processSignoutResponse"), { state: r, response: n } = await this.readSignoutResponseState(t, !0);
    return r ? (e.debug("Received state from storage; validating response"), this._validator.validateSignoutResponse(n, r)) : e.debug("No state from storage; skipping response validation"), n;
  }
  clearStaleState() {
    return this._logger.create("clearStaleState"), Cs.clearStaleState(this.settings.stateStore, this.settings.staleStateAgeInSeconds);
  }
  async revokeToken(t, e) {
    return this._logger.create("revokeToken"), await this._tokenClient.revoke({ token: t, token_type_hint: e });
  }
}, Nw = class {
  constructor(t) {
    this._userManager = t, this._logger = new de("SessionMonitor"), this._start = async (e) => {
      const r = e.session_state;
      if (!r) return;
      const n = this._logger.create("_start");
      if (e.profile ? (this._sub = e.profile.sub, n.debug("session_state", r, ", sub", this._sub)) : (this._sub = void 0, n.debug("session_state", r, ", anonymous user")), this._checkSessionIFrame) this._checkSessionIFrame.start(r);
      else try {
        const s = await this._userManager.metadataService.getCheckSessionIframe();
        if (s) {
          n.debug("initializing check session iframe");
          const i = this._userManager.settings.client_id, o = this._userManager.settings.checkSessionIntervalInSeconds, a = this._userManager.settings.stopCheckSessionOnError, c = new Sw(this._callback, i, s, o, a);
          await c.load(), this._checkSessionIFrame = c, c.start(r);
        } else n.warn("no check session iframe found in the metadata");
      } catch (s) {
        n.error("Error from getCheckSessionIframe:", s instanceof Error ? s.message : s);
      }
    }, this._stop = () => {
      const e = this._logger.create("_stop");
      if (this._sub = void 0, this._checkSessionIFrame && this._checkSessionIFrame.stop(), this._userManager.settings.monitorAnonymousSession) {
        const r = setInterval(async () => {
          clearInterval(r);
          try {
            const n = await this._userManager.querySessionStatus();
            if (n) {
              const s = { session_state: n.session_state, profile: n.sub ? { sub: n.sub } : null };
              this._start(s);
            }
          } catch (n) {
            e.error("error from querySessionStatus", n instanceof Error ? n.message : n);
          }
        }, 1e3);
      }
    }, this._callback = async () => {
      const e = this._logger.create("_callback");
      try {
        const r = await this._userManager.querySessionStatus();
        let n = !0;
        r && this._checkSessionIFrame ? r.sub === this._sub ? (n = !1, this._checkSessionIFrame.start(r.session_state), e.debug("same sub still logged in at OP, session state has changed, restarting check session iframe; session_state", r.session_state), await this._userManager.events._raiseUserSessionChanged()) : e.debug("different subject signed into OP", r.sub) : e.debug("subject no longer signed into OP"), n ? this._sub ? await this._userManager.events._raiseUserSignedOut() : await this._userManager.events._raiseUserSignedIn() : e.debug("no change in session detected, no event to raise");
      } catch (r) {
        this._sub && (e.debug("Error calling queryCurrentSigninSession; raising signed out event", r), await this._userManager.events._raiseUserSignedOut());
      }
    }, t || this._logger.throw(new Error("No user manager passed")), this._userManager.events.addUserLoaded(this._start), this._userManager.events.addUserUnloaded(this._stop), this._init().catch((e) => {
      this._logger.error(e);
    });
  }
  async _init() {
    this._logger.create("_init");
    const t = await this._userManager.getUser();
    if (t) this._start(t);
    else if (this._userManager.settings.monitorAnonymousSession) {
      const e = await this._userManager.querySessionStatus();
      if (e) {
        const r = { session_state: e.session_state, profile: e.sub ? { sub: e.sub } : null };
        this._start(r);
      }
    }
  }
}, _s = class rh {
  constructor(e) {
    var r;
    this.id_token = e.id_token, this.session_state = (r = e.session_state) != null ? r : null, this.access_token = e.access_token, this.refresh_token = e.refresh_token, this.token_type = e.token_type, this.scope = e.scope, this.profile = e.profile, this.expires_at = e.expires_at, this.state = e.userState, this.url_state = e.url_state;
  }
  get expires_in() {
    if (this.expires_at !== void 0) return this.expires_at - Ot.getEpochTime();
  }
  set expires_in(e) {
    e !== void 0 && (this.expires_at = Math.floor(e) + Ot.getEpochTime());
  }
  get expired() {
    const e = this.expires_in;
    if (e !== void 0) return e <= 0;
  }
  get scopes() {
    var e, r;
    return (r = (e = this.scope) == null ? void 0 : e.split(" ")) != null ? r : [];
  }
  toStorageString() {
    return new de("User").create("toStorageString"), JSON.stringify({ id_token: this.id_token, session_state: this.session_state, access_token: this.access_token, refresh_token: this.refresh_token, token_type: this.token_type, scope: this.scope, profile: this.profile, expires_at: this.expires_at });
  }
  static fromStorageString(e) {
    return de.createStatic("User", "fromStorageString"), new rh(JSON.parse(e));
  }
}, ec = "oidc-client", nh = class {
  constructor() {
    this._abort = new qt("Window navigation aborted"), this._disposeHandlers = /* @__PURE__ */ new Set(), this._window = null;
  }
  async navigate(t) {
    const e = this._logger.create("navigate");
    if (!this._window) throw new Error("Attempted to navigate on a disposed window");
    e.debug("setting URL in window"), this._window.location.replace(t.url);
    const { url: r, keepOpen: n } = await new Promise((s, i) => {
      const o = (c) => {
        var u;
        const l = c.data, h = (u = t.scriptOrigin) != null ? u : window.location.origin;
        if (c.origin === h && l?.source === ec) {
          try {
            const p = to.readParams(l.url, t.response_mode).get("state");
            if (p || e.warn("no state found in response url"), c.source !== this._window && p !== t.state) return;
          } catch {
            this._dispose(), i(new Error("Invalid response from window"));
          }
          s(l);
        }
      };
      window.addEventListener("message", o, !1), this._disposeHandlers.add(() => window.removeEventListener("message", o, !1));
      const a = new BroadcastChannel(`oidc-client-popup-${t.state}`);
      a.addEventListener("message", o, !1), this._disposeHandlers.add(() => a.close()), this._disposeHandlers.add(this._abort.addHandler((c) => {
        this._dispose(), i(c);
      }));
    });
    return e.debug("got response from window"), this._dispose(), n || this.close(), { url: r };
  }
  _dispose() {
    this._logger.create("_dispose");
    for (const t of this._disposeHandlers) t();
    this._disposeHandlers.clear();
  }
  static _notifyParent(t, e, r = !1, n = window.location.origin) {
    const s = { source: ec, url: e, keepOpen: r }, i = new de("_notifyParent");
    if (t) i.debug("With parent. Using parent.postMessage."), t.postMessage(s, n);
    else {
      i.debug("No parent. Using BroadcastChannel.");
      const o = new URL(e).searchParams.get("state");
      if (!o) throw new Error("No parent and no state in URL. Can't complete notification.");
      const a = new BroadcastChannel(`oidc-client-popup-${o}`);
      a.postMessage(s), a.close();
    }
  }
}, sh = { location: !1, toolbar: !1, height: 640, closePopupWindowAfterInSeconds: -1 }, ih = "_blank", xw = 60, zw = 2, jw = class extends no {
  constructor(t) {
    const { popup_redirect_uri: e = t.redirect_uri, popup_post_logout_redirect_uri: r = t.post_logout_redirect_uri, popupWindowFeatures: n = sh, popupWindowTarget: s = ih, redirectMethod: i = "assign", redirectTarget: o = "self", iframeNotifyParentOrigin: a = t.iframeNotifyParentOrigin, iframeScriptOrigin: c = t.iframeScriptOrigin, requestTimeoutInSeconds: u, silent_redirect_uri: l = t.redirect_uri, silentRequestTimeoutInSeconds: h, automaticSilentRenew: p = !0, validateSubOnSilentRenew: g = !0, includeIdTokenInSilentRenew: S = !1, monitorSession: k = !1, monitorAnonymousSession: y = !1, checkSessionIntervalInSeconds: b = zw, query_status_response_type: m = "code", stopCheckSessionOnError: w = !0, revokeTokenTypes: $ = ["access_token", "refresh_token"], revokeTokensOnSignout: d = !1, includeIdTokenInSilentSignout: f = !1, accessTokenExpiringNotificationTimeInSeconds: _ = xw, userStore: E } = t;
    if (super(t), this.popup_redirect_uri = e, this.popup_post_logout_redirect_uri = r, this.popupWindowFeatures = n, this.popupWindowTarget = s, this.redirectMethod = i, this.redirectTarget = o, this.iframeNotifyParentOrigin = a, this.iframeScriptOrigin = c, this.silent_redirect_uri = l, this.silentRequestTimeoutInSeconds = h || u || 10, this.automaticSilentRenew = p, this.validateSubOnSilentRenew = g, this.includeIdTokenInSilentRenew = S, this.monitorSession = k, this.monitorAnonymousSession = y, this.checkSessionIntervalInSeconds = b, this.stopCheckSessionOnError = w, this.query_status_response_type = m, this.revokeTokenTypes = $, this.revokeTokensOnSignout = d, this.includeIdTokenInSilentSignout = f, this.accessTokenExpiringNotificationTimeInSeconds = _, E) this.userStore = E;
    else {
      const z = typeof window < "u" ? window.sessionStorage : new Kd();
      this.userStore = new ha({ store: z });
    }
  }
}, tc = class oh extends nh {
  constructor({ silentRequestTimeoutInSeconds: e = 10 }) {
    super(), this._logger = new de("IFrameWindow"), this._timeoutInSeconds = e, this._frame = oh.createHiddenIframe(), this._window = this._frame.contentWindow;
  }
  static createHiddenIframe() {
    const e = window.document.createElement("iframe");
    return e.style.visibility = "hidden", e.style.position = "fixed", e.style.left = "-1000px", e.style.top = "0", e.width = "0", e.height = "0", window.document.body.appendChild(e), e;
  }
  async navigate(e) {
    this._logger.debug("navigate: Using timeout of:", this._timeoutInSeconds);
    const r = setTimeout(() => {
      this._abort.raise(new la("IFrame timed out without a response"));
    }, 1e3 * this._timeoutInSeconds);
    return this._disposeHandlers.add(() => clearTimeout(r)), await super.navigate(e);
  }
  close() {
    var e;
    this._frame && (this._frame.parentNode && (this._frame.addEventListener("load", (r) => {
      var n;
      const s = r.target;
      (n = s.parentNode) == null || n.removeChild(s), this._abort.raise(new Error("IFrame removed from DOM"));
    }, !0), (e = this._frame.contentWindow) == null || e.location.replace("about:blank")), this._frame = null), this._window = null;
  }
  static notifyParent(e, r) {
    return super._notifyParent(window.parent, e, !1, r);
  }
}, Mw = class {
  constructor(t) {
    this._settings = t, this._logger = new de("IFrameNavigator");
  }
  async prepare({ silentRequestTimeoutInSeconds: t = this._settings.silentRequestTimeoutInSeconds }) {
    return new tc({ silentRequestTimeoutInSeconds: t });
  }
  async callback(t) {
    this._logger.create("callback"), tc.notifyParent(t, this._settings.iframeNotifyParentOrigin);
  }
}, rc = class extends nh {
  constructor({ popupWindowTarget: t = ih, popupWindowFeatures: e = {}, popupSignal: r, popupAbortOnClose: n }) {
    super(), this._logger = new de("PopupWindow");
    const s = Xa.center({ ...sh, ...e });
    this._window = window.open(void 0, t, Xa.serialize(s)), this.abortOnClose = !!n, r && r.addEventListener("abort", () => {
      var i;
      this._abort.raise(new Error((i = r.reason) != null ? i : "Popup aborted"));
    }), e.closePopupWindowAfterInSeconds && e.closePopupWindowAfterInSeconds > 0 && setTimeout(() => {
      this._window && typeof this._window.closed == "boolean" && !this._window.closed ? this.close() : this._abort.raise(new Error("Popup blocked by user"));
    }, 1e3 * e.closePopupWindowAfterInSeconds);
  }
  async navigate(t) {
    var e;
    (e = this._window) == null || e.focus();
    const r = setInterval(() => {
      this._window && !this._window.closed || (this._logger.debug("Popup closed by user or isolated by redirect"), n(), this._disposeHandlers.delete(n), this.abortOnClose && this._abort.raise(new Error("Popup closed by user")));
    }, 500), n = () => clearInterval(r);
    return this._disposeHandlers.add(n), await super.navigate(t);
  }
  close() {
    this._window && (this._window.closed || (this._window.close(), this._abort.raise(new Error("Popup closed")))), this._window = null;
  }
  static notifyOpener(t, e) {
    super._notifyParent(window.opener, t, e), e || window.opener || window.close();
  }
}, qw = class {
  constructor(t) {
    this._settings = t, this._logger = new de("PopupNavigator");
  }
  async prepare({ popupWindowFeatures: t = this._settings.popupWindowFeatures, popupWindowTarget: e = this._settings.popupWindowTarget, popupSignal: r, popupAbortOnClose: n }) {
    return new rc({ popupWindowFeatures: t, popupWindowTarget: e, popupSignal: r, popupAbortOnClose: n });
  }
  async callback(t, { keepOpen: e = !1 }) {
    this._logger.create("callback"), rc.notifyOpener(t, e);
  }
}, Uw = class {
  constructor(t) {
    this._settings = t, this._logger = new de("RedirectNavigator");
  }
  async prepare({ redirectMethod: t = this._settings.redirectMethod, redirectTarget: e = this._settings.redirectTarget }) {
    var r;
    this._logger.create("prepare");
    let n = window.self;
    e === "top" && (n = (r = window.top) != null ? r : window.self);
    const s = n.location[t].bind(n.location);
    let i;
    return { navigate: async (o) => (this._logger.create("navigate"), await new Promise((c, u) => {
      i = u, window.addEventListener("pageshow", () => c(window.location.href)), s(o.url);
    })), close: () => {
      this._logger.create("close"), i?.(new Error("Redirect aborted")), n.stop();
    } };
  }
  async callback() {
  }
}, Dw = class extends bw {
  constructor(t) {
    super({ expiringNotificationTimeInSeconds: t.accessTokenExpiringNotificationTimeInSeconds }), this._logger = new de("UserManagerEvents"), this._userLoaded = new qt("User loaded"), this._userUnloaded = new qt("User unloaded"), this._silentRenewError = new qt("Silent renew error"), this._userSignedIn = new qt("User signed in"), this._userSignedOut = new qt("User signed out"), this._userSessionChanged = new qt("User session changed");
  }
  async load(t, e = !0) {
    await super.load(t), e && await this._userLoaded.raise(t);
  }
  async unload() {
    await super.unload(), await this._userUnloaded.raise();
  }
  addUserLoaded(t) {
    return this._userLoaded.addHandler(t);
  }
  removeUserLoaded(t) {
    return this._userLoaded.removeHandler(t);
  }
  addUserUnloaded(t) {
    return this._userUnloaded.addHandler(t);
  }
  removeUserUnloaded(t) {
    return this._userUnloaded.removeHandler(t);
  }
  addSilentRenewError(t) {
    return this._silentRenewError.addHandler(t);
  }
  removeSilentRenewError(t) {
    return this._silentRenewError.removeHandler(t);
  }
  async _raiseSilentRenewError(t) {
    await this._silentRenewError.raise(t);
  }
  addUserSignedIn(t) {
    return this._userSignedIn.addHandler(t);
  }
  removeUserSignedIn(t) {
    this._userSignedIn.removeHandler(t);
  }
  async _raiseUserSignedIn() {
    await this._userSignedIn.raise();
  }
  addUserSignedOut(t) {
    return this._userSignedOut.addHandler(t);
  }
  removeUserSignedOut(t) {
    this._userSignedOut.removeHandler(t);
  }
  async _raiseUserSignedOut() {
    await this._userSignedOut.raise();
  }
  addUserSessionChanged(t) {
    return this._userSessionChanged.addHandler(t);
  }
  removeUserSessionChanged(t) {
    this._userSessionChanged.removeHandler(t);
  }
  async _raiseUserSessionChanged() {
    await this._userSessionChanged.raise();
  }
}, Lw = class {
  constructor(t) {
    this._userManager = t, this._logger = new de("SilentRenewService"), this._isStarted = !1, this._retryTimer = new Ot("Retry Silent Renew"), this._tokenExpiring = async () => {
      const e = this._logger.create("_tokenExpiring");
      try {
        await this._userManager.signinSilent(), e.debug("silent token renewal successful");
      } catch (r) {
        if (r instanceof la) return e.warn("ErrorTimeout from signinSilent:", r, "retry in 5s"), void this._retryTimer.init(5);
        e.error("Error from signinSilent:", r), await this._userManager.events._raiseSilentRenewError(r);
      }
    };
  }
  async start() {
    const t = this._logger.create("start");
    if (!this._isStarted) {
      this._isStarted = !0, this._userManager.events.addAccessTokenExpiring(this._tokenExpiring), this._retryTimer.addHandler(this._tokenExpiring);
      try {
        await this._userManager.getUser();
      } catch (e) {
        t.error("getUser error", e);
      }
    }
  }
  stop() {
    this._isStarted && (this._retryTimer.cancel(), this._retryTimer.removeHandler(this._tokenExpiring), this._userManager.events.removeAccessTokenExpiring(this._tokenExpiring), this._isStarted = !1);
  }
}, Zw = class {
  constructor(t) {
    this.refresh_token = t.refresh_token, this.id_token = t.id_token, this.session_state = t.session_state, this.scope = t.scope, this.profile = t.profile, this.data = t.state;
  }
}, Hw = class {
  constructor(t, e, r, n) {
    this._logger = new de("UserManager"), this.settings = new jw(t), this._client = new Aw(t), this._redirectNavigator = e ?? new Uw(this.settings), this._popupNavigator = r ?? new qw(this.settings), this._iframeNavigator = n ?? new Mw(this.settings), this._events = new Dw(this.settings), this._silentRenewService = new Lw(this), this.settings.automaticSilentRenew && this.startSilentRenew(), this._sessionMonitor = null, this.settings.monitorSession && (this._sessionMonitor = new Nw(this));
  }
  get events() {
    return this._events;
  }
  get metadataService() {
    return this._client.metadataService;
  }
  async getUser(t = !1) {
    const e = this._logger.create("getUser"), r = await this._loadUser();
    return r ? (e.info("user loaded"), await this._events.load(r, t), r) : (e.info("user not found in storage"), null);
  }
  async removeUser() {
    const t = this._logger.create("removeUser");
    await this.storeUser(null), t.info("user removed from storage"), await this._events.unload();
  }
  async signinRedirect(t = {}) {
    var e;
    this._logger.create("signinRedirect");
    const { redirectMethod: r, ...n } = t;
    let s;
    (e = this.settings.dpop) != null && e.bind_authorization_code && (s = await this.generateDPoPJkt(this.settings.dpop));
    const i = await this._redirectNavigator.prepare({ redirectMethod: r });
    await this._signinStart({ request_type: "si:r", dpopJkt: s, ...n }, i);
  }
  async signinRedirectCallback(t = window.location.href) {
    const e = this._logger.create("signinRedirectCallback"), r = await this._signinEnd(t);
    return r.profile && r.profile.sub ? e.info("success, signed in subject", r.profile.sub) : e.info("no subject"), r;
  }
  async signinResourceOwnerCredentials({ username: t, password: e, skipUserInfo: r = !1 }) {
    const n = this._logger.create("signinResourceOwnerCredential"), s = await this._client.processResourceOwnerPasswordCredentials({ username: t, password: e, skipUserInfo: r, extraTokenParams: this.settings.extraTokenParams });
    n.debug("got signin response");
    const i = await this._buildUser(s);
    return i.profile && i.profile.sub ? n.info("success, signed in subject", i.profile.sub) : n.info("no subject"), i;
  }
  async signinPopup(t = {}) {
    var e;
    const r = this._logger.create("signinPopup");
    let n;
    (e = this.settings.dpop) != null && e.bind_authorization_code && (n = await this.generateDPoPJkt(this.settings.dpop));
    const { popupWindowFeatures: s, popupWindowTarget: i, popupSignal: o, popupAbortOnClose: a, ...c } = t, u = this.settings.popup_redirect_uri;
    u || r.throw(new Error("No popup_redirect_uri configured"));
    const l = await this._popupNavigator.prepare({ popupWindowFeatures: s, popupWindowTarget: i, popupSignal: o, popupAbortOnClose: a }), h = await this._signin({ request_type: "si:p", redirect_uri: u, display: "popup", dpopJkt: n, ...c }, l);
    return h && (h.profile && h.profile.sub ? r.info("success, signed in subject", h.profile.sub) : r.info("no subject")), h;
  }
  async signinPopupCallback(t = window.location.href, e = !1) {
    const r = this._logger.create("signinPopupCallback");
    await this._popupNavigator.callback(t, { keepOpen: e }), r.info("success");
  }
  async signinSilent(t = {}) {
    var e, r;
    const n = this._logger.create("signinSilent"), { silentRequestTimeoutInSeconds: s, ...i } = t;
    let o, a = await this._loadUser();
    if (!t.forceIframeAuth && a?.refresh_token) {
      n.debug("using refresh token");
      const h = new Zw(a);
      return await this._useRefreshToken({ state: h, redirect_uri: i.redirect_uri, resource: i.resource, extraTokenParams: i.extraTokenParams, timeoutInSeconds: s });
    }
    (e = this.settings.dpop) != null && e.bind_authorization_code && (o = await this.generateDPoPJkt(this.settings.dpop));
    const c = this.settings.silent_redirect_uri;
    let u;
    c || n.throw(new Error("No silent_redirect_uri configured")), a && this.settings.validateSubOnSilentRenew && (n.debug("subject prior to silent renew:", a.profile.sub), u = a.profile.sub);
    const l = await this._iframeNavigator.prepare({ silentRequestTimeoutInSeconds: s });
    return a = await this._signin({ request_type: "si:s", redirect_uri: c, prompt: "none", id_token_hint: this.settings.includeIdTokenInSilentRenew ? a?.id_token : void 0, dpopJkt: o, ...i }, l, u), a && ((r = a.profile) != null && r.sub ? n.info("success, signed in subject", a.profile.sub) : n.info("no subject")), a;
  }
  async _useRefreshToken(t) {
    const e = await this._client.useRefreshToken({ timeoutInSeconds: this.settings.silentRequestTimeoutInSeconds, ...t }), r = new _s({ ...t.state, ...e });
    return await this.storeUser(r), await this._events.load(r), r;
  }
  async signinSilentCallback(t = window.location.href) {
    const e = this._logger.create("signinSilentCallback");
    await this._iframeNavigator.callback(t), e.info("success");
  }
  async signinCallback(t = window.location.href) {
    const { state: e } = await this._client.readSigninResponseState(t);
    switch (e.request_type) {
      case "si:r":
        return await this.signinRedirectCallback(t);
      case "si:p":
        await this.signinPopupCallback(t);
        break;
      case "si:s":
        await this.signinSilentCallback(t);
        break;
      default:
        throw new Error("invalid response_type in state");
    }
  }
  async signoutCallback(t = window.location.href, e = !1) {
    const { state: r } = await this._client.readSignoutResponseState(t);
    if (r) switch (r.request_type) {
      case "so:r":
        return await this.signoutRedirectCallback(t);
      case "so:p":
        await this.signoutPopupCallback(t, e);
        break;
      case "so:s":
        await this.signoutSilentCallback(t);
        break;
      default:
        throw new Error("invalid response_type in state");
    }
  }
  async querySessionStatus(t = {}) {
    const e = this._logger.create("querySessionStatus"), { silentRequestTimeoutInSeconds: r, ...n } = t, s = this.settings.silent_redirect_uri;
    s || e.throw(new Error("No silent_redirect_uri configured"));
    const i = await this._loadUser(), o = await this._iframeNavigator.prepare({ silentRequestTimeoutInSeconds: r }), a = await this._signinStart({ request_type: "si:s", redirect_uri: s, prompt: "none", id_token_hint: this.settings.includeIdTokenInSilentRenew ? i?.id_token : void 0, response_type: this.settings.query_status_response_type, scope: "openid", skipUserInfo: !0, ...n }, o);
    try {
      const c = {}, u = await this._client.processSigninResponse(a.url, c);
      return e.debug("got signin response"), u.session_state && u.profile.sub ? (e.info("success for subject", u.profile.sub), { session_state: u.session_state, sub: u.profile.sub }) : (e.info("success, user not authenticated"), null);
    } catch (c) {
      if (this.settings.monitorAnonymousSession && c instanceof nr) switch (c.error) {
        case "login_required":
        case "consent_required":
        case "interaction_required":
        case "account_selection_required":
          return e.info("success for anonymous user"), { session_state: c.session_state };
      }
      throw c;
    }
  }
  async _signin(t, e, r) {
    const n = await this._signinStart(t, e);
    return await this._signinEnd(n.url, r);
  }
  async _signinStart(t, e) {
    const r = this._logger.create("_signinStart");
    try {
      const n = await this._client.createSigninRequest(t);
      return r.debug("got signin request"), await e.navigate({ url: n.url, state: n.state.id, response_mode: n.state.response_mode, scriptOrigin: this.settings.iframeScriptOrigin });
    } catch (n) {
      throw r.debug("error after preparing navigator, closing navigator window"), e.close(), n;
    }
  }
  async _signinEnd(t, e) {
    const r = this._logger.create("_signinEnd"), n = await this._client.processSigninResponse(t, {});
    return r.debug("got signin response"), await this._buildUser(n, e);
  }
  async _buildUser(t, e) {
    const r = this._logger.create("_buildUser"), n = new _s(t);
    if (e) {
      if (e !== n.profile.sub) throw r.debug("current user does not match user returned from signin. sub from signin:", n.profile.sub), new nr({ ...t, error: "login_required" });
      r.debug("current user matches user returned from signin");
    }
    return await this.storeUser(n), r.debug("user stored"), await this._events.load(n), n;
  }
  async signoutRedirect(t = {}) {
    const e = this._logger.create("signoutRedirect"), { redirectMethod: r, ...n } = t, s = await this._redirectNavigator.prepare({ redirectMethod: r });
    await this._signoutStart({ request_type: "so:r", post_logout_redirect_uri: this.settings.post_logout_redirect_uri, ...n }, s), e.info("success");
  }
  async signoutRedirectCallback(t = window.location.href) {
    const e = this._logger.create("signoutRedirectCallback"), r = await this._signoutEnd(t);
    return e.info("success"), r;
  }
  async signoutPopup(t = {}) {
    const e = this._logger.create("signoutPopup"), { popupWindowFeatures: r, popupWindowTarget: n, popupSignal: s, ...i } = t, o = this.settings.popup_post_logout_redirect_uri, a = await this._popupNavigator.prepare({ popupWindowFeatures: r, popupWindowTarget: n, popupSignal: s });
    await this._signout({ request_type: "so:p", post_logout_redirect_uri: o, state: o == null ? void 0 : {}, ...i }, a), e.info("success");
  }
  async signoutPopupCallback(t = window.location.href, e = !1) {
    const r = this._logger.create("signoutPopupCallback");
    await this._popupNavigator.callback(t, { keepOpen: e }), r.info("success");
  }
  async _signout(t, e) {
    const r = await this._signoutStart(t, e);
    return await this._signoutEnd(r.url);
  }
  async _signoutStart(t = {}, e) {
    var r;
    const n = this._logger.create("_signoutStart");
    try {
      const s = await this._loadUser();
      n.debug("loaded current user from storage"), this.settings.revokeTokensOnSignout && await this._revokeInternal(s);
      const i = t.id_token_hint || s && s.id_token;
      i && (n.debug("setting id_token_hint in signout request"), t.id_token_hint = i), await this.removeUser(), n.debug("user removed, creating signout request");
      const o = await this._client.createSignoutRequest(t);
      return n.debug("got signout request"), await e.navigate({ url: o.url, state: (r = o.state) == null ? void 0 : r.id, scriptOrigin: this.settings.iframeScriptOrigin });
    } catch (s) {
      throw n.debug("error after preparing navigator, closing navigator window"), e.close(), s;
    }
  }
  async _signoutEnd(t) {
    const e = this._logger.create("_signoutEnd"), r = await this._client.processSignoutResponse(t);
    return e.debug("got signout response"), r;
  }
  async signoutSilent(t = {}) {
    var e;
    const r = this._logger.create("signoutSilent"), { silentRequestTimeoutInSeconds: n, ...s } = t, i = this.settings.includeIdTokenInSilentSignout ? (e = await this._loadUser()) == null ? void 0 : e.id_token : void 0, o = this.settings.popup_post_logout_redirect_uri, a = await this._iframeNavigator.prepare({ silentRequestTimeoutInSeconds: n });
    await this._signout({ request_type: "so:s", post_logout_redirect_uri: o, id_token_hint: i, ...s }, a), r.info("success");
  }
  async signoutSilentCallback(t = window.location.href) {
    const e = this._logger.create("signoutSilentCallback");
    await this._iframeNavigator.callback(t), e.info("success");
  }
  async revokeTokens(t) {
    const e = await this._loadUser();
    await this._revokeInternal(e, t);
  }
  async _revokeInternal(t, e = this.settings.revokeTokenTypes) {
    const r = this._logger.create("_revokeInternal");
    if (!t) return;
    const n = e.filter((s) => typeof t[s] == "string");
    if (n.length) {
      for (const s of n) await this._client.revokeToken(t[s], s), r.info(`${s} revoked successfully`), s !== "access_token" && (t[s] = null);
      await this.storeUser(t), r.debug("user stored"), await this._events.load(t);
    } else r.debug("no need to revoke due to no token(s)");
  }
  startSilentRenew() {
    this._logger.create("startSilentRenew"), this._silentRenewService.start();
  }
  stopSilentRenew() {
    this._silentRenewService.stop();
  }
  get _userStoreKey() {
    return `user:${this.settings.authority}:${this.settings.client_id}`;
  }
  async _loadUser() {
    const t = this._logger.create("_loadUser"), e = await this.settings.userStore.get(this._userStoreKey);
    return e ? (t.debug("user storageString loaded"), _s.fromStorageString(e)) : (t.debug("no user storageString"), null);
  }
  async storeUser(t) {
    const e = this._logger.create("storeUser");
    if (t) {
      e.debug("storing user");
      const r = t.toStorageString();
      await this.settings.userStore.set(this._userStoreKey, r);
    } else this._logger.debug("removing user"), await this.settings.userStore.remove(this._userStoreKey), this.settings.dpop && await this.settings.dpop.store.remove(this.settings.client_id);
  }
  async clearStaleState() {
    await this._client.clearStaleState();
  }
  async dpopProof(t, e, r, n) {
    var s, i;
    const o = await ((i = (s = this.settings.dpop) == null ? void 0 : s.store) == null ? void 0 : i.get(this.settings.client_id));
    if (o) return await Ue.generateDPoPProof({ url: t, accessToken: e?.access_token, httpMethod: r, keyPair: o.keys, nonce: n });
  }
  async generateDPoPJkt(t) {
    let e = await t.store.get(this.settings.client_id);
    if (!e) {
      const r = await Ue.generateDPoPKeys();
      e = new th(r), await t.store.set(this.settings.client_id, e);
    }
    return await Ue.generateDPoPJkt(e.keys);
  }
};
const ah = "OAUTH2_LOGIN_FLOW_COMPLETE_EVENT", ch = "OAUTH_GET_TOP_URL", oo = "OAUTH_REDIRECT_TOP_WINDOW", uh = "OAUTH_UPDATE_URL", lh = "OAUTH2_CHECK_PENDING", sn = "oauth2_top_origin", Pr = "oauth2_login_success", on = "oauth2_state", ao = 60, Fw = Math.max(ao - 15, 20), Vw = ca("oidc-auth", { color: "green" }), ei = (t) => Vw.extend(t);
ca("oidc-auth-utils");
const nc = () => typeof window > "u" ? "" : new URLSearchParams(window.location.search).get("origin") || "";
class yr {
  static instance = null;
  settings = null;
  constructor() {
  }
  static getInstance() {
    return yr.instance || (yr.instance = new yr()), yr.instance;
  }
  configure(e) {
    this.settings = e;
  }
  isConfigured() {
    return this.settings !== null;
  }
  getSettings() {
    if (!this.settings) throw new Error("OidcAuthConfig not configured. Call configure() or pass settings to OidcAuthClient.initialize().");
    return this.settings;
  }
  getAuthOrigin() {
    const { authOrigin: e, authEndpoint: r } = this.getSettings();
    return e || new URL(r).origin;
  }
  isAccessTokenProactiveRefreshEnabled() {
    return this.settings?.accessTokenProactiveRefreshEnabled ?? !0;
  }
  getOidcSettings() {
    const e = typeof window > "u" ? "" : window.location.origin, { clientId: r, authEndpoint: n } = this.getSettings(), s = this.getAuthOrigin(), i = typeof window < "u" ? new ha({ store: window.localStorage }) : void 0, { accessTokenExpiringNotificationTimeInSeconds: o = ao } = this.getSettings();
    return { client_id: r, authority: s, redirect_uri: `${e}/login/oauth-callback`, post_logout_redirect_uri: e, response_type: "code", scope: "openid offline_access", automaticSilentRenew: !1, accessTokenExpiringNotificationTimeInSeconds: o, stateStore: i, userStore: i, metadata: { issuer: s, authorization_endpoint: n, token_endpoint: `${s}/connect/api/v1/oauth2/token`, end_session_endpoint: `${s}/logout/` } };
  }
  getAccessTokenExpiringNotificationTimeInSeconds() {
    return this.getSettings().accessTokenExpiringNotificationTimeInSeconds ?? ao;
  }
  getAccessTokenFreshnessThresholdInSeconds() {
    return this.getSettings().accessTokenFreshnessThresholdInSeconds ?? Fw;
  }
  getAllowedParentOrigins() {
    return this.settings?.allowedParentOrigins;
  }
}
const vt = yr.getInstance(), Bw = ei("oidc-auth:host-api"), Dr = async (t) => new Promise((e, r) => {
  const n = new MessageChannel();
  let s = !1;
  const i = () => {
    s = !0, n.port1.close();
  }, o = setTimeout(() => {
    s || (i(), r(new Error(`Host message timeout: ${t.type}`)));
  }, 1e4);
  n.port1.onmessage = (c) => {
    clearTimeout(o), i(), c.data.status !== "success" ? r(c.data.payload) : e(c.data.payload);
  };
  const a = new URLSearchParams(window.location.search).get("origin") || "";
  if (!(function(c) {
    if (!c.startsWith("http://") && !c.startsWith("https://")) return !1;
    const u = vt.getAllowedParentOrigins();
    return !u || u.length === 0 || u.includes(c);
  })(a)) return clearTimeout(o), i(), void r(new Error("Origin not allowed"));
  Bw.log("posting message to host", t), window.top.postMessage({ type: t.type, payload: t.payload, ...t.data || {} }, a, [n.port2]);
}), Ww = ei("oidc-auth:OidcAuthTimer");
class Gw {
  timerHandle = null;
  expiration = null;
  initialized = !1;
  callback = () => {
  };
  constructor() {
    this.timerHandle = null;
  }
  init(e, r, n) {
    const s = e - this.getEpochTime(), i = Math.max(s - r, 10);
    this.cancel(), this.expiration = i, this.callback = n, Ww.debug("OIDC: timer - using expiration", i, s, r, e, s - r), this.timerHandle = setTimeout(this.callback, 1e3 * i), this.initialized = !0;
  }
  cancel() {
    this.timerHandle && (clearTimeout(this.timerHandle), this.timerHandle = null), this.expiration = null;
  }
  getEpochTime() {
    return Math.floor(Date.now() / 1e3);
  }
  isInitialized() {
    return this.initialized;
  }
}
const me = ei("oidc-auth:OidcAuthClient");
class wr {
  static instance = null;
  userManager = null;
  initialized = !1;
  accessTokenExpiringTimer = null;
  retryTimers = /* @__PURE__ */ new Set();
  constructor() {
  }
  static getInstance() {
    return wr.instance || (wr.instance = new wr()), wr.instance;
  }
  isInitialized() {
    return this.initialized;
  }
  ensureInitialized() {
    if (!this.userManager) throw new Error("OidcAuthClient not initialized. Call initialize() first.");
    return this.userManager;
  }
  initialize(e) {
    if (e && (this.initialized = !1, vt.configure(e)), this.initialized) me.info("OIDC: initialize() - already initialized, skipping");
    else if (typeof window < "u") if (vt.isConfigured()) try {
      me.info("OIDC: initialize() - starting initialization");
      const r = vt.getOidcSettings();
      this.userManager = new Hw(r), tr.setLogger(me), tr.setLevel(tr.ERROR), this.initAccessTokenExpiringTimer(), this.initialized = !0;
    } catch (r) {
      throw me.error("OIDC: initialize() - FAILED:", r), r;
    }
    else me.warn("OIDC: initialize() - skipped, config not set");
    else me.warn("OidcAuthClient cannot initialize on server side");
  }
  async initAccessTokenExpiringTimer() {
    vt.isAccessTokenProactiveRefreshEnabled() ? this.getUser().then((e) => {
      const r = e?.expires_at;
      r && (this.accessTokenExpiringTimer || (this.accessTokenExpiringTimer = new Gw()), this.accessTokenExpiringTimer.init(r, vt.getAccessTokenExpiringNotificationTimeInSeconds(), async () => {
        me.info("OIDC: timer proactive refresh access token expiring timer fired", r), this.proactiveRefreshWithRetry();
      }));
    }).catch((e) => {
      me.error("OIDC: initAccessTokenExpiringTimer - FAILED:", e);
    }) : me.warn("OIDC: timer - not starting, access token proactive refresh is disabled");
  }
  async getUser() {
    if (!this.userManager) return null;
    try {
      return await this.userManager.getUser();
    } catch (e) {
      return me.error("OIDC: getUser - FAILED:", e), null;
    }
  }
  async storeUser(e) {
    await this.ensureInitialized().storeUser(e);
  }
  async getAccessToken() {
    const e = await this.getUser();
    if (!e) return me.info("OIDC: getAccessToken - no user found"), null;
    if (e.expired) try {
      return (await this.signinSilent())?.access_token || null;
    } catch (r) {
      return me.error("OIDC: getAccessToken - silent renew failed:", r), null;
    }
    return this.isTokenFresh(e) || this.signinSilent().catch((r) => {
      me.error("OIDC: getAccessToken - background refresh failed:", r);
    }), e.access_token;
  }
  getUserData() {
    if (typeof window > "u") return null;
    try {
      const e = vt.getOidcSettings(), r = `oidc.user:${e.authority}:${e.client_id}`, n = localStorage.getItem(r);
      if (!n) return null;
      const s = JSON.parse(n), i = s?.profile;
      return i?.sub ? (me.info("OIDC: USER:", { profile: i }), { id: i.sub, email: i.email || "", first_name: i.given_name, last_name: i.family_name }) : null;
    } catch (e) {
      return me.error("OIDC: getUserData - FAILED:", e), null;
    }
  }
  async isAuthenticated() {
    const e = await this.getUser();
    return e !== null && !e.expired;
  }
  async signinRedirect(e) {
    await this.ensureInitialized().signinRedirect({ state: e ? { data: e } : void 0, prompt: "login" });
  }
  async signinCallback() {
    const e = this.ensureInitialized(), r = await e.signinCallback();
    if (!r) throw me.error("OIDC: signinCallback - FAILED: no user returned"), new Error("Signin callback failed: no user returned");
    return r;
  }
  async signinSilent(e) {
    return this.ensureInitialized(), typeof navigator < "u" && navigator.locks ? navigator.locks.request("oidc-token-refresh", async () => {
      const r = await this.getUser();
      return r && this.isTokenFresh(r, e) ? r : this.doSigninSilent();
    }) : (me.warn("OIDC: signinSilent - navigator.locks not available, proceeding without lock"), this.doSigninSilent());
  }
  isTokenFresh(e, r) {
    if (!e.expires_at) return !1;
    const n = r ?? vt.getAccessTokenFreshnessThresholdInSeconds(), s = Math.floor(Date.now() / 1e3);
    return e.expires_at - s > n;
  }
  async doSigninSilent() {
    const e = this.ensureInitialized();
    try {
      return await e.signinSilent();
    } catch (r) {
      throw me.error("OIDC: doSigninSilent - FAILED:", r), r;
    }
  }
  proactiveRefreshWithRetry(e = 1) {
    if (typeof document < "u" && document.visibilityState === "hidden") {
      me.info("OIDC: tab is hidden, deferring proactive refresh until visible");
      const r = () => {
        document.visibilityState === "visible" && (document.removeEventListener("visibilitychange", r), this.proactiveRefreshWithRetry(e));
      };
      return void document.addEventListener("visibilitychange", r);
    }
    this.signinSilent(vt.getAccessTokenExpiringNotificationTimeInSeconds()).then(() => {
      this.initAccessTokenExpiringTimer();
    }).catch((r) => {
      if (me.error(`OIDC: proactive refresh failed (attempt ${e}/2):`, r), e < 2) {
        const n = setTimeout(() => {
          this.retryTimers.delete(n), this.proactiveRefreshWithRetry(e + 1);
        }, 3e3);
        this.retryTimers.add(n);
      } else me.error("OIDC: proactive refresh exhausted all retries");
    });
  }
  async removeUser() {
    const e = this.ensureInitialized();
    this.accessTokenExpiringTimer?.cancel(), this.retryTimers.forEach(clearTimeout), this.retryTimers.clear(), await e.removeUser();
  }
  onUserLoaded(e) {
    this.ensureInitialized().events.addUserLoaded(e);
  }
  offUserLoaded(e) {
    this.ensureInitialized().events.removeUserLoaded(e);
  }
  onUserUnloaded(e) {
    this.ensureInitialized().events.addUserUnloaded(e);
  }
  offUserUnloaded(e) {
    this.ensureInitialized().events.removeUserUnloaded(e);
  }
  onSilentRenewError(e) {
    this.ensureInitialized().events.addSilentRenewError(e);
  }
  offSilentRenewError(e) {
    this.ensureInitialized().events.removeSilentRenewError(e);
  }
  onAccessTokenExpiring(e) {
    this.ensureInitialized().events.addAccessTokenExpiring(e);
  }
  offAccessTokenExpiring(e) {
    this.ensureInitialized().events.removeAccessTokenExpiring(e);
  }
  onAccessTokenExpired(e) {
    this.ensureInitialized().events.addAccessTokenExpired(e);
  }
  offAccessTokenExpired(e) {
    this.ensureInitialized().events.removeAccessTokenExpired(e);
  }
  getLogoutUrl(e, r) {
    const n = new URL((function(s) {
      return `${vt.getAuthOrigin()}${s.logoutPath}`;
    })(e));
    return r && n.searchParams.set("redirect_to", r), n.toString();
  }
  getWindowOriginParam() {
    const e = new URL(window.location.href).searchParams.get("origin");
    if (!e) throw new Error("iframe origin param is required");
    return e;
  }
  async getTopUrl() {
    return (await Dr({ type: ch })).topUrl;
  }
  async isOAuthFlowPending() {
    try {
      return (await Dr({ type: lh })).isPending;
    } catch (e) {
      return me.warn("OIDC: isOAuthFlowPending() - failed to check, assuming not pending:", e), !1;
    }
  }
  async triggerLoginFlowViaParent({ loginPath: e, windowPath: r }) {
    me.info("OIDC: triggerLoginFlowViaParent() - starting");
    const n = await this.getTopUrl(), s = new URL(n).origin, i = `${s}${r}`, o = new URL(`${window.location.origin}${e}`);
    o.searchParams.set(sn, s), o.searchParams.set("oauth2_top_wp_url", i), me.info("OIDC: triggerLoginFlowViaParent() - redirecting parent to:", o.toString()), await Dr({ type: oo, payload: { url: o.toString() } });
  }
  async handleLoginFlowComplete(e, r) {
    if (!r) throw new Error("oauthUserState is required");
    const n = this.getWindowOriginParam(), s = r.state, i = s?.data?.[sn];
    if (n !== i) throw me.error("OIDC: handleLoginFlowComplete - origin mismatch:", n, "!==", i), new Error("Invalid origin in OAuth state");
    try {
      const o = new _s(r);
      await this.storeUser(o), this.initAccessTokenExpiringTimer(), window.dispatchEvent(new CustomEvent("oidc-auth-completed"));
    } catch (o) {
      me.error("OIDC: handleLoginFlowComplete - FAILED to store user:", o), await this.triggerLoginFlowViaParent(e);
    }
  }
  async triggerLogoutViaParent(e, r = !0) {
    const n = await this.getTopUrl(), s = new URL(n).origin, i = r ? `${s}${e.windowPath}` : s;
    await this.removeUser();
    const o = this.getLogoutUrl(e, i);
    await Dr({ type: oo, payload: { url: o } });
  }
  async cleanOAuthParamsFromUrl() {
    try {
      const e = await this.getTopUrl(), r = new URL(e);
      r.searchParams.delete("oauth_code"), r.searchParams.delete("oauth_state"), r.searchParams.delete("start-oauth"), r.searchParams.delete(Pr), r.searchParams.delete(on), await Dr({ type: uh, payload: { url: r.toString() } });
    } catch (e) {
      me.warn("Failed to clean OAuth params from URL:", e);
    }
  }
  setupLoginFlowMessageListener(e) {
    let r = !1;
    const n = (s) => {
      if (s.data?.type !== ah) return;
      if (s.origin !== nc()) return void me.error("OIDC: origin mismatch - expected:", nc(), "received:", s.origin);
      if (r) return void me.debug("OIDC: LOGIN_FLOW_COMPLETE already processed, ignoring duplicate");
      const i = s.data.payload;
      i?.oauthState ? (r = !0, this.handleLoginFlowComplete(e, i.oauthState).catch((o) => {
        me.error("OIDC: Failed to handle login flow complete:", o), r = !1;
      })) : me.warn("OIDC: LOGIN_FLOW_COMPLETE but no oauthState in payload");
    };
    return window.addEventListener("message", n), () => {
      window.removeEventListener("message", n);
    };
  }
  async getTokenExpirationInfo() {
    const e = await this.getUser();
    if (!e || !e.expires_at) return { expiresAt: null, expiresInSeconds: null, isExpired: !0 };
    const r = new Date(1e3 * e.expires_at), n = Date.now(), s = Math.floor((1e3 * e.expires_at - n) / 1e3);
    return { expiresAt: r, expiresInSeconds: s, isExpired: s <= 0 };
  }
  async forceTokenRefresh() {
    return me.info("OIDC: forceTokenRefresh() - manually triggering token refresh"), this.signinSilent();
  }
}
const Jw = wr.getInstance();
typeof window < "u" && (window.oidcAuthClient = Jw);
const Bt = ei("oidc-auth:oidc-auth-redirect");
function mi(t, e) {
  t.postMessage({ status: "success", payload: e });
}
function sc(t, e) {
  t.postMessage({ status: "error", payload: e });
}
function co({ targets: t, onSuccess: e, attempt: r = 1 }) {
  const n = new URLSearchParams(window.location.search);
  if (!n.get(Pr)) return void Bt.warn("OIDC: No login_success param found, skipping");
  const s = n.get(on);
  if (s) {
    if (!t.window?.contentWindow || !t.windowURL) return Bt.warn("Cannot forward OIDC state: iframe not available"), void (r < 5 ? setTimeout(() => {
      co({ targets: t, onSuccess: e, attempt: r + 1 });
    }, 500) : Bt.error("OIDC: Failed to forward login flow after", 5, "attempts - iframe never became available"));
    try {
      const i = JSON.parse(s), o = i.state?.data?.[sn];
      if (o && o !== window.location.origin) return void Bt.error("Origin mismatch in OIDC state:", o, "vs", window.location.origin);
      (function(c, u) {
        const l = u.window?.contentWindow, h = u.windowURL?.origin;
        l && h ? l.postMessage({ type: ah, payload: c }, h) : Bt.warn("Cannot send OIDC state: window or origin not available");
      })({ oauthState: i }, t);
      const a = new URL(window.location.href);
      a.searchParams.delete(Pr), a.searchParams.delete(on), history.replaceState({}, "", a.toString()), e?.();
    } catch (i) {
      Bt.error("Failed to parse or forward OIDC state:", i);
    }
  } else Bt.warn("OIDC login complete but no state found in URL");
}
const dh = "angie_return_url", Kr = $t("referrer-redirect");
function Kw(t) {
  try {
    return new URL(t, window.location.origin).origin === window.location.origin;
  } catch {
    return !1;
  }
}
function hh() {
  try {
    const t = localStorage.getItem(dh);
    if (!t) return null;
    let e;
    try {
      e = JSON.parse(t);
    } catch {
      return Kr.warn("Stored redirect data is not valid JSON, returning null"), null;
    }
    return e.url && typeof e.url == "string" ? Kw(e.url) ? e : (Kr.warn("Stored redirect URL is invalid, returning null:", e.url), null) : (Kr.warn("Stored redirect data missing url field, returning null"), null);
  } catch {
    return Kr.warn("localStorage not available"), null;
  }
}
function fh() {
  try {
    localStorage.removeItem(dh);
  } catch {
    Kr.warn("localStorage not available");
  }
}
function ph(t, e) {
  return e ? `${t}#angie-prompt=${encodeURIComponent(e)}` : t;
}
function Qw() {
  const t = hh();
  return !!t && (fh(), window.location.href = ph(t.url, t.prompt), !0);
}
const fa = $t("oauth"), mh = () => {
  (() => {
    try {
      const t = new URL(window.location.href, window.location.origin).searchParams;
      return t.has("start-oauth") && t.get("page") === "angie-app";
    } catch {
      return !1;
    }
  })() && (fa.log("Post-consent flow detected, checking for referrer redirect"), Qw());
};
function gi() {
  const t = hh();
  if (t) return fh(), void (window.location.href = ph(t.url, t.prompt));
  try {
    localStorage.setItem("angie_sidebar_state", "open");
  } catch {
    fa.warn("localStorage not available");
  }
  setTimeout(() => {
    window.toggleAngieSidebar(!0);
  }, 500);
}
const ti = (t, e) => {
  const r = document.getElementById(ge.containerId);
  r && r.setAttribute("aria-hidden", e ? "false" : "true"), e ? t.removeAttribute("tabindex") : t.setAttribute("tabindex", "-1");
}, Dt = (t, e) => {
  t.postMessage({ status: "success", payload: e });
}, Rn = $t("sdk");
var ic;
(ic || (ic = {})).POST_MESSAGE = "postMessage";
const kn = $t("sidebar");
let _i = !1;
const an = "open", cn = "closed";
function gh() {
  if (typeof window > "u") return 370;
  try {
    const t = window.localStorage.getItem("angie_sidebar_width");
    if (t) {
      const e = parseInt(t, 10);
      if (e >= 350 && e <= 590) return e;
    }
  } catch {
    kn.warn("localStorage not available");
  }
  return 370;
}
function _h() {
  return typeof window > "u" ? null : localStorage.getItem("angie_sidebar_state");
}
function Yw(t) {
  try {
    localStorage.setItem("angie_sidebar_state", t);
  } catch {
    kn.warn("localStorage not available");
  }
}
function Xw(t) {
  try {
    localStorage.setItem("angie_sidebar_width", t.toString());
  } catch {
    kn.warn("localStorage not available");
  }
}
function oc(t) {
  document.documentElement.style.setProperty("--angie-sidebar-width", `${t}px`);
}
function ev(t = an) {
  (function() {
    if (typeof window > "u") return !1;
    const e = new URLSearchParams(window.location.search);
    return e.has(Pr) || e.has(on) || e.has(sn);
  })() ? (function() {
    uo(cn);
    try {
      localStorage.setItem("angie_sidebar_state", cn);
    } catch {
      kn.warn("localStorage not available");
    }
  })() : uo(_h() || t);
}
function uo(t) {
  typeof window < "u" && window.toggleAngieSidebar && window.toggleAngieSidebar(t === an, !0);
}
function tv() {
  const t = document.getElementById(ge.containerId);
  if (!t) return;
  let e = !1, r = 0, n = 0;
  t.addEventListener("mousedown", (s) => {
    const i = t.getBoundingClientRect();
    (document.documentElement.dir === "rtl" ? s.clientX <= i.left + 4 : s.clientX >= i.right - 4) && (e = !0, r = s.clientX, n = i.width, t.classList.add("angie-resizing"), document.body.style.cursor = "ew-resize", document.body.style.userSelect = "none", s.preventDefault(), s.stopPropagation());
  }), document.addEventListener("mousemove", (s) => {
    if (!e) return;
    let i;
    i = document.documentElement.dir === "rtl" ? r - s.clientX : s.clientX - r, oc(Math.max(350, Math.min(590, n + i))), s.preventDefault(), s.stopPropagation();
  }), document.addEventListener("mouseup", (s) => {
    if (e) {
      e = !1, t.classList.remove("angie-resizing"), document.body.style.cursor = "", document.body.style.userSelect = "";
      const i = parseInt(getComputedStyle(document.documentElement).getPropertyValue("--angie-sidebar-width"), 10);
      Xw(i), Tr({ type: Ie.ANGIE_SIDEBAR_RESIZED, payload: { initialWidth: n, width: i } }), s.preventDefault(), s.stopPropagation();
    }
  }), oc(gh());
}
let ac = !1;
function yh(t) {
  var e;
  t?.skipDefaultCss || (function() {
    if (typeof document > "u" || _i) return;
    const r = "angie-sidebar-styles";
    if (document.getElementById(r)) return void (_i = !0);
    const n = document.createElement("style");
    n.id = r, n.textContent = `/* Angie Sidebar - CSS Variables */
:root {
    --angie-sidebar-z-index: 1200; /* below MUI popups, elementor popups and media library modal */
    --angie-sidebar-width: 330px;
    --angie-sidebar-transition: margin 0.3s ease-in-out, transform 0.3s ease-in-out;
    /* Direction-aware transform values for sidebar positioning */
    --angie-sidebar-hide-transform: translateX(-100%); /* LTR: hide to the left */
    --angie-sidebar-show-transform: translateX(0);
}

/* RTL-specific transform values */
[dir="rtl"] {
    --angie-sidebar-hide-transform: translateX(100%); /* RTL: hide to the right */
}

/* Respect user's motion preferences */
@media (prefers-reduced-motion: reduce) {
    :root {
        --angie-sidebar-transition: none;
    }
}

/* Apply transitions only when user is actively toggling */
body.angie-sidebar-transitioning {
    transition: var(--angie-sidebar-transition) !important;
}

body.angie-sidebar-transitioning #angie-sidebar-container {
    transition: var(--angie-sidebar-transition) !important;
}

/* Layout (default) - Push content */
@media (min-width: 768px) {
    body.angie-sidebar-active {
        padding-inline-start: var(--angie-sidebar-width) !important;
    }

    #angie-sidebar-container {
        position: fixed;
        top: 0;
        inset-inline-start: 0;
        width: var(--angie-sidebar-width);
        height: 100vh;
        z-index: var(--angie-sidebar-z-index) !important; /* below elementor popups and media library modal */
        background: #FCFCFC;
        transform: var(--angie-sidebar-hide-transform);
        outline: none;
        overflow: hidden;
        /* No default transition - only when transitioning */
    }

    /* Resize handle */
    #angie-sidebar-container::after {
        content: '';
        position: absolute;
        top: 0;
        inset-inline-end: 0;
        width: 4px;
        height: 100%;
        cursor: ew-resize;
        background: transparent;
        z-index: 1000001;
    }

    /* Pink border during resize */
    #angie-sidebar-container.angie-resizing {
        border-inline-end-color: #ff69b4 !important;
        border-inline-end-width: 2px !important;
    }

    /* Disable iframe pointer events during resize */
    #angie-sidebar-container.angie-resizing iframe#angie-iframe {
        pointer-events: none !important;
    }
}

/* Active states */
body.angie-sidebar-active #angie-sidebar-container {
    transform: var(--angie-sidebar-show-transform);
}

/* Studio mode - sidebar takes full width */
@media (min-width: 768px) {
    html.angie-studio-active body.angie-sidebar-active #angie-sidebar-container {
        width: 100%;
    }
}

/* High contrast mode support */
@media (prefers-contrast: high) {
    #angie-sidebar-container {
        border-color: #000;
        box-shadow: none;
    }
}

/* Screen reader only class */
.angie-sr-only {
    position: absolute;
    width: 1px;
    height: 1px;
    padding: 0;
    margin: -1px;
    overflow: hidden;
    clip: rect(0, 0, 0, 0);
    white-space: nowrap;
    border: 0;
}

/* Plugin conflict resolution */
body.angie-sidebar-active {
    /* Reset common conflicting styles */
    box-sizing: border-box !important;
    position: relative !important;
}

#angie-sidebar-toggle {
    z-index: 99999 !important;
}
`;
    const s = document.head || document.getElementsByTagName("head")[0];
    s.insertBefore(n, s.firstChild), _i = !0;
  })(), typeof window < "u" && (window.toggleAngieSidebar = (e = t?.onToggle, function(r, n) {
    const s = document.body, i = document.getElementById(ge.containerId);
    if (!i) return void kn.warn("Required elements not found!");
    const o = s.classList.contains("angie-sidebar-active"), a = r !== void 0 ? r : !o;
    n || (s.classList.add("angie-sidebar-transitioning"), setTimeout(function() {
      s.classList.remove("angie-sidebar-transitioning");
    }, 300)), a ? s.classList.add("angie-sidebar-active") : s.classList.remove("angie-sidebar-active"), ge.iframe && ti(ge.iframe, a), a && setTimeout(function() {
      Tr({ type: "focusInput" });
    }, n ? 0 : 300), e && e(a, i, n), Yw(a ? an : cn);
    const c = new CustomEvent("angieSidebarToggle", { detail: { isOpen: a, sidebar: i, skipTransition: n } });
    document.dispatchEvent(c), Tr({ type: Ie.ANGIE_SIDEBAR_TOGGLED, payload: { state: a ? "opened" : "closed" } });
  }), ac || (ac = !0, window.addEventListener("message", function(r) {
    if (r.data?.type !== "toggleAngieSidebar") return;
    const n = ge.iframeUrlObject?.origin;
    if (n && r.origin !== n) return;
    const { force: s, skipTransition: i } = r.data.payload || {};
    window.toggleAngieSidebar && window.toggleAngieSidebar(s, i);
    const o = r.ports?.[0];
    o && Dt(o);
  })));
}
const at = $t("iframe"), rv = (t) => {
  if (t.includes("://") || t.startsWith("//")) return !1;
  try {
    const e = "https://test.com";
    return new URL(t, e).origin === e;
  } catch {
    return !1;
  }
}, cc = async () => {
  if (ge.iframe?.contentWindow && ge.iframeUrlObject) try {
    at.log("Disabling navigation prevention in Angie iframe"), ge.iframe.contentWindow.postMessage({ type: Ie.ANGIE_DISABLE_NAVIGATION_PREVENTION }, ge.iframeUrlObject.origin), await new Promise((t) => setTimeout(t, 100));
  } catch (t) {
    throw at.error("Failed to disable navigation prevention:", t), t;
  }
  else at.warn("Cannot disable navigation prevention: iframe or origin not available");
}, wh = async (t) => {
  if (window.screen.availWidth <= 768) return void at.log("Mobile detected, skipping iframe injection");
  let e = document.getElementById(ge.containerId);
  if (!e) {
    const s = performance.now();
    if (at.log("⏱️ Waiting for sidebar container..."), await new Promise((i) => {
      let o = 0;
      const a = setInterval(() => {
        e = document.getElementById(ge.containerId), o++, (e || o > 20) && (clearInterval(a), e && i());
      }, 100);
      setTimeout(() => {
        if (clearInterval(a), e) return void i();
        const c = new MutationObserver(() => {
          e = document.getElementById(ge.containerId), e && (c.disconnect(), i());
        });
        c.observe(document.body, { childList: !0, subtree: !0 }), setTimeout(() => {
          c.disconnect(), i();
        }, 8e3);
      }, 2e3);
    }), at.log(`⏱️ Sidebar container detection took: ${(performance.now() - s).toFixed(2)}ms`), !e) return void at.error("Sidebar container not found");
  }
  const { iframe: r, iframeUrlObject: n } = await (async (s) => {
    const i = s.origin, o = new URL(s.path, i), a = o.pathname.slice(1).replace(/\//, "--") + "-" + Math.random().toString(36).substring(7);
    return new Promise((c) => {
      const u = new URL(i);
      u.pathname = o.pathname;
      const l = window.matchMedia("(prefers-color-scheme: dark)").matches ? "dark" : "light";
      if (u.searchParams.append("colorScheme", s.uiTheme || l || "light"), u.searchParams.append("sdkVersion", s.sdkVersion), u.searchParams.append("instanceId", a), u.searchParams.append("origin", window.location.origin), s.isRTL && u.searchParams.append("isRTL", s.isRTL ? "true" : "false"), window.location.hostname === "localhost" && window.location.search.includes("debug_error")) {
        const S = new URLSearchParams(window.location.search).get("debug_error");
        S && u.searchParams.append("debug_error", S);
      }
      o.searchParams.forEach((S, k) => {
        u.searchParams.set(k, S);
      }), u.searchParams.set("ver", (/* @__PURE__ */ new Date()).getTime().toString());
      const h = s.parent || document, p = h.createElement("iframe"), g = { "background-color": "transparent", "color-scheme": "normal", ...s.css };
      window.addEventListener("message", async (S) => {
        if (S.origin === u.origin) switch (S.data.type) {
          case _r.ANGIE_READY:
            c({ iframe: p, iframeUrlObject: u });
            break;
          case _r.ANGIE_LOADED:
            p.contentWindow?.postMessage({ type: _r.HOST_READY, instanceId: a, ...s.embeddedConfig ? { embedded: s.embeddedConfig } : {} }, u.origin);
        }
      }), p.setAttribute("src", u.href), p.id = "angie-iframe", p.setAttribute("frameborder", "0"), p.setAttribute("scrolling", "no"), p.setAttribute("style", Object.entries(g).map(([S, k]) => `${S}: ${k}`).join("; ")), p.setAttribute("allow", "clipboard-write; clipboard-read"), s.insertCallback ? s.insertCallback(p) : h.body.appendChild(p);
    });
  })({ origin: t.origin || "https://angie.elementor.com", path: t.path && rv(t.path) ? t.path : "angie/wp-admin", insertCallback: (s) => {
    at.log("Injecting Angie iframe into sidebar container"), s.setAttribute("title", "Angie AI Assistant"), s.setAttribute("role", "application"), s.setAttribute("aria-label", "Angie AI Assistant Interface");
    const i = document.getElementById("angie-sidebar-loading");
    i && (i.textContent = ""), e?.appendChild(s);
  }, embeddedConfig: t.embeddedConfig, css: { width: "100%", height: "100%", border: "none", outline: "none" }, uiTheme: t.uiTheme, isRTL: t.isRTL, sdkVersion: "1.5.0" });
  ge.iframe = r, ge.iframeUrlObject = n, ((s) => {
    Gr = s;
  })(r), window.addEventListener("message", (s) => {
    if (s.origin === ge.iframeUrlObject?.origin) switch (s.data.type) {
      case Rr.SET:
        window.localStorage.setItem(s.data.key, s.data.value);
        break;
      case Rr.GET: {
        const i = s.ports[0], o = window.localStorage.getItem(s.data.key);
        i.postMessage({ value: o });
        break;
      }
    }
  }), ((s) => {
    window.addEventListener("message", async (i) => {
      const o = i.origin === window.location.origin, a = i.origin === s.iframeUrlObject?.origin;
      if (o || a) switch (i?.data?.type) {
        case Ie.SDK_ANGIE_ALL_SERVERS_REGISTERED:
          break;
        case Ie.SDK_ANGIE_READY_PING: {
          const c = i.ports[0];
          Rn.log("Angie is ready", i), Dt(c, { message: "Angie is ready" });
          break;
        }
        case Ie.SDK_REQUEST_CLIENT_CREATION: {
          const c = i.data.payload;
          try {
            const u = i.ports[0], l = new MessageChannel();
            l.port1.onmessage = (p) => {
              u.postMessage({ success: !0, data: p.data });
            };
            const h = { type: Ie.SDK_REQUEST_CLIENT_CREATION, payload: { success: !0, ...c, clientId: `dynamic-client-${c.serverName}-${c.serverVersion}-${Date.now()}`, requestId: i.data.payload.requestId }, timestamp: Date.now() };
            if (!s.iframe) throw new Error("Iframe not found");
            s.iframe.contentWindow?.postMessage(h, s.iframeUrlObject?.origin || "", [l.port2]);
          } catch (u) {
            Rn.error(`Failed to create client for SDK server "${c.serverName}":`, u);
          }
          break;
        }
        case Ie.SDK_TRIGGER_ANGIE:
          Rn.log("SDK Trigger Angie received", i.data);
          try {
            const { requestId: c, prompt: u, context: l, options: h } = i.data.payload;
            if (!s.iframe) throw new Error("Iframe not found");
            s.iframe.contentWindow?.postMessage({ type: Ie.SDK_TRIGGER_ANGIE, payload: { requestId: c, prompt: u, context: l, options: h } }, s.iframeUrlObject?.origin || ""), window.postMessage({ type: Ie.SDK_TRIGGER_ANGIE_RESPONSE, payload: { success: !0, requestId: c, response: "Angie triggered successfully" } }, window.location.origin);
          } catch (c) {
            Rn.error("Failed to trigger Angie:", c), window.postMessage({ type: Ie.SDK_TRIGGER_ANGIE_RESPONSE, payload: { success: !1, requestId: i.data.payload?.requestId, error: c instanceof Error ? c.message : "Unknown error" } }, window.location.origin);
          }
      }
    });
  })(ge), (function({ trustedOrigin: s, onOAuthParamsCleared: i }) {
    window.addEventListener("message", (o) => {
      if (o.origin !== s) return;
      const a = o.ports?.[0];
      switch (o.data.type) {
        case ch:
          if (!a) return;
          mi(a, { topUrl: window.location.href });
          break;
        case oo:
          window.location.href = o.data.payload.url;
          break;
        case uh: {
          if (!a) return;
          const c = o.data.payload.url;
          if (!history?.replaceState) return void sc(a, { message: "URL update not supported in this browser" });
          try {
            const u = window.location.href;
            history.replaceState({}, "", c), (function(l, h) {
              const p = new URL(l).searchParams, g = new URL(h).searchParams, S = [Pr, on, sn];
              return S.some((k) => p.has(k)) && !S.some((k) => g.has(k));
            })(u, c) && i?.(), mi(a, { message: "URL updated successfully" });
          } catch (u) {
            sc(a, { message: "URL update failed: " + (u instanceof Error ? u.message : "Unknown error") });
          }
          break;
        }
        case lh:
          if (!a) return;
          mi(a, { isPending: new URLSearchParams(window.location.search).get(Pr) === "true" });
      }
    });
  })({ trustedOrigin: ge.iframeUrlObject?.origin ?? "", onOAuthParamsCleared: gi }), (() => {
    const s = { window: ge.iframe, windowURL: ge.iframeUrlObject };
    window.addEventListener("load", () => {
      fa.log("OIDC: Window load event fired, forwarding OIDC state if present"), co({ targets: s, onSuccess: gi });
    }), co({ targets: s, onSuccess: gi });
  })(), window.addEventListener("message", async (s) => {
    if ([window.location.origin, t.origin || "https://angie.elementor.com"].includes(s.origin)) if (s?.data?.type === Ie.ANGIE_CHAT_TOGGLE) ge.open = s.data.open, ge.iframe && ti(ge.iframe, ge.open);
    else if (s?.data?.type === Ie.ANGIE_STUDIO_TOGGLE) {
      const i = s.data.isStudioOpen;
      if (!ge.iframe) return;
      if (i) document.documentElement.classList.add("angie-studio-active");
      else {
        const o = gh();
        document.documentElement.style.setProperty("--angie-sidebar-width", `${o}px`), document.documentElement.classList.remove("angie-studio-active");
      }
    } else if (s?.data?.type === Ie.ANGIE_NAVIGATE_TO_URL) {
      const { url: i = "", confirmed: o = !1 } = s.data.payload || {};
      if (!o) return void at.log("Navigation requires user confirmation");
      if (!((a, c = []) => {
        const u = c.length === 0 && typeof window < "u" ? [window.location.origin] : c;
        if (!a.startsWith("http")) return !1;
        try {
          const l = new URL(a);
          return u.includes(l.origin);
        } catch {
          return !1;
        }
      })(i)) return void at.error("Navigation blocked: Invalid or unsafe URL", { url: i });
      await cc(), window.location.assign(i);
    } else if (s?.data?.type === Ie.ANGIE_PAGE_RELOAD) {
      const { confirmed: i = !1 } = s.data.payload || {};
      if (!i) return void at.log("Page reload requires user confirmation");
      at.log("Page reload confirmed - disabling navigation prevention and reloading"), await cc(), setTimeout(() => {
        window.location.reload();
      }, 50);
    } else s?.data?.type === _r.RESET_HASH && (window.location.hash = "", Dt(s.ports[0], { message: "Hash reset successfully" }));
  });
}, Et = $t("registration-queue");
class nv {
  queue = [];
  isProcessing = !1;
  add(e) {
    const r = { id: this.generateId(e), config: e, timestamp: Date.now(), status: "pending" };
    return this.queue.push(r), Et.log(`Added server "${e.name}" to queue`), r;
  }
  getAll() {
    return [...this.queue];
  }
  getPending() {
    return this.queue.filter((e) => e.status === "pending");
  }
  updateStatus(e, r, n) {
    const s = this.queue.find((i) => i.id === e);
    s && (s.status = r, n ? s.error = n : r !== "pending" && r !== "registered" || delete s.error, Et.log(`Updated server ${e} status to ${r}`));
  }
  async processQueue(e) {
    if (this.isProcessing) return void Et.log("Already processing queue");
    this.isProcessing = !0;
    const r = this.getPending();
    Et.log(`Processing ${r.length} pending registrations`);
    try {
      for (const n of r) try {
        await e(n), this.updateStatus(n.id, "registered");
      } catch (s) {
        const i = s instanceof Error ? s.message : String(s);
        this.updateStatus(n.id, "failed", i), Et.error(`Failed to process registration ${n.id}:`, i);
      }
    } finally {
      this.isProcessing = !1;
    }
  }
  clear() {
    this.queue = [], Et.log("Cleared all registrations");
  }
  resetAllToPending() {
    if (this.isProcessing) return Et.log("Cannot reset to pending - processing in progress"), !1;
    const e = this.queue.filter((n) => n.status === "registered").length, r = this.queue.filter((n) => n.status === "failed").length;
    return this.queue.forEach((n) => {
      n.status !== "pending" && (n.status = "pending", delete n.error);
    }), Et.log(`Reset ${e + r} registrations to pending`), !0;
  }
  remove(e) {
    const r = this.queue.findIndex((n) => n.id === e);
    return r !== -1 && (this.queue.splice(r, 1), Et.log(`Removed registration ${e}`), !0);
  }
  generateId(e) {
    return `reg_${e.name}_${e.version}_${Date.now()}`;
  }
}
const vh = "sidebar", un = "floatingChat", bh = { layout: vh, styleTheme: "", persistOpenState: !0, resizable: !0, chatToggleButtonEnabled: !1 }, ar = { boot: { allowInIframe: !1 }, container: { layout: bh.layout, chatToggleButtonSelector: "#angie-widget-toggle" }, iframe: { origin: "https://angie.elementor.com", path: "angie/embedded", uiTheme: "light" } };
let hr = null, uc = !1;
const sv = async (t) => {
  if (!hr || t.origin !== hr.iframeOrigin) return;
  const e = t.data?.type, r = t.ports?.[0];
  switch (e) {
    case "GET_EXTERNAL_HEADERS":
      if (!r) return;
      await (async (s, i) => {
        try {
          const o = i ? await i() : {};
          Dt(s, ((a) => Object.fromEntries(Object.entries(a).filter(([, c]) => c !== void 0)))(o));
        } catch (o) {
          ((a, c) => {
            a.postMessage({ status: "error", payload: c });
          })(s, { message: o instanceof Error ? o.message : String(o) });
        }
      })(r, hr.getExternalHeaders);
      break;
    case "angie/context/get-website-context":
      if (!r) return;
      Dt(r, (n = hr.host, { payload: { name: document.title, tagline: "", homeUrl: window.location.origin, siteLang: document.documentElement.lang, docTitle: document.title, platform: "frontend", timezone: Intl.DateTimeFormat().resolvedOptions().timeZone, today: (/* @__PURE__ */ new Date()).toISOString().split("T")[0], ...n?.website } }));
      break;
    case "angie/context/get-analytics-context":
      if (!r) return;
      Dt(r, ((s) => ({ payload: { screenPath: window.location.pathname, ...s?.analytics } }))(hr.host));
      break;
    case Rr.GET:
      if (!r) return;
      ((s, i) => {
        try {
          const o = window.localStorage?.getItem(i) ?? null;
          s.postMessage({ value: o });
        } catch {
          s.postMessage({ value: null });
        }
      })(r, t.data.key);
      break;
    case Rr.SET:
      ((s, i) => {
        try {
          window.localStorage?.setItem(s, i);
        } catch {
        }
      })(t.data.key, t.data.value);
  }
  var n;
}, lc = "data-angie-toggle-wired", pa = (t, e) => {
  const r = document.querySelector(t);
  r && (r.setAttribute("aria-expanded", e ? "true" : "false"), r.setAttribute("aria-label", e ? "Close Angie" : "Open Angie"));
}, Sh = (t) => {
  const e = document.querySelector(t.toggleButtonSelector);
  e && e.getAttribute(lc) !== "true" && (e.setAttribute(lc, "true"), e.addEventListener("click", t.onClick));
}, lo = /* @__PURE__ */ new Set();
let dc = !1;
const iv = (t) => {
  for (const e of lo) e(t);
}, Qr = "angie-widget-toggle", ln = "angie-widget-hidden", Os = "angie-widget-fullscreen", Mt = "angie-widget-container", hc = "angie-chat-widget-styles", ov = /^#([^\s#.[:]+)$/, av = /^\[([^\]=]+)(?:="([^"]*)")?\]$/, yi = (t) => document.querySelector(t);
let fc = null;
const rr = (t) => {
  const e = document.getElementById(t.containerId);
  e && (t.isOpen ? e.classList.remove(ln) : e.classList.add(ln), ge.iframe && ti(ge.iframe, t.isOpen), pa(t.toggleButtonSelector, t.isOpen));
}, kh = (t, e) => {
  const r = e?.force;
  if (r === !1) return rr({ containerId: t.containerId, toggleButtonSelector: t.toggleButtonSelector, isOpen: !1 }), void t.onClose?.();
  if (r === !0) return void rr({ containerId: t.containerId, toggleButtonSelector: t.toggleButtonSelector, isOpen: !0 });
  const n = document.getElementById(t.containerId), s = n && !n.classList.contains(ln);
  rr({ containerId: t.containerId, toggleButtonSelector: t.toggleButtonSelector, isOpen: !s }), s && t.onClose?.();
}, cv = (t) => {
  var e;
  fc?.(), e = (r) => {
    if (r.origin !== t.iframeOrigin) return;
    const n = r.ports?.[0], { type: s, payload: i } = r.data || {};
    switch (s) {
      case Ie.ANGIE_SIDEBAR_TOGGLED:
      case "toggleAngieSidebar":
        kh(t, i), n && Dt(n);
        break;
      case Ie.ANGIE_STUDIO_TOGGLE: {
        const o = !!r.data.isStudioOpen;
        ((a, c) => {
          const u = document.getElementById(a);
          u && (c ? u.classList.add(Os) : u.classList.remove(Os));
        })(t.containerId, o), o && rr({ containerId: t.containerId, toggleButtonSelector: t.toggleButtonSelector, isOpen: !0 }), n && Dt(n);
        break;
      }
    }
  }, lo.add(e), dc || (dc = !0, window.addEventListener("message", iv)), fc = () => {
    lo.delete(e);
  };
}, uv = (t) => {
  if (document.getElementById(hc)) return;
  const e = document.createElement("style");
  e.id = hc, e.textContent = ((r) => `
#${r}.${Mt} {
	--angie-widget-width: 400px;
	--angie-widget-height: 600px;
	--angie-widget-z-index: 99999;

	position: fixed !important;
	top: auto !important;
	bottom: 20px !important;
	inset-inline-start: auto !important;
	inset-inline-end: 20px !important;
	width: var(--angie-widget-width) !important;
	height: var(--angie-widget-height) !important;
	max-height: calc(100vh - 40px) !important;
	max-width: calc(100vw - 40px) !important;
	z-index: var(--angie-widget-z-index) !important;
	transform: none !important;
	border-radius: 12px !important;
	overflow: hidden !important;
	box-shadow: 0 4px 24px rgba(0, 0, 0, 0.15) !important;
	transition: opacity 0.2s ease, transform 0.2s ease !important;
}

#${r}.${Mt}.${ln} {
	display: none !important;
}

#${r}.${Mt} iframe {
	width: 100% !important;
	height: 100% !important;
	border: none !important;
	border-radius: 12px !important;
}


.${Qr} {
	--angie-toggle-size: 56px;
	--angie-widget-z-index: 99999;

	position: fixed;
	bottom: 20px;
	inset-inline-end: 20px;
	width: var(--angie-toggle-size);
	height: var(--angie-toggle-size);
	border-radius: 50%;
	border: none;
	background: #EB8EFB;
	color: white;
	cursor: pointer;
	z-index: var(--angie-widget-z-index);
	display: flex;
	align-items: center;
	justify-content: center;
	box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
	transition: background 0.2s ease, transform 0.15s ease;
	padding: 0;
}

.${Qr}:hover {
	background: #E070F5;
	transform: scale(1.05);
}

.${Qr}:active {
	transform: scale(0.95);
}

.${Qr}[aria-expanded="true"] {
	display: none;
}


#${r}.${Mt}.${Os} {
	bottom: 0 !important;
	inset-inline-end: 0 !important;
	width: 100vw !important;
	height: 100vh !important;
	max-height: 100vh !important;
	max-width: 100vw !important;
	border-radius: 0 !important;
}

#${r}.${Mt}.${Os} iframe {
	border-radius: 0 !important;
}

@media (max-width: 480px) {
	#${r}.${Mt} {
		bottom: 0 !important;
		inset-inline-end: 0 !important;
		width: 100vw !important;
		height: 100vh !important;
		max-height: 100vh !important;
		max-width: 100vw !important;
		border-radius: 0 !important;
	}

	#${r}.${Mt} iframe {
		border-radius: 0 !important;
	}
}
`)(t), document.head.appendChild(e);
}, lv = (t) => {
  uv(t.containerId), ((e) => {
    const r = document.getElementById(e);
    r && (r.classList.add(Mt, ln), r.setAttribute("role", "complementary"), r.setAttribute("aria-label", "Angie"), r.setAttribute("aria-hidden", "true"), r.setAttribute("tabindex", "-1"));
  })(t.containerId), t.injectToggleButton && ((e) => {
    if (yi(e)) return;
    const r = document.createElement("button");
    ((n, s) => {
      const i = s.match(ov);
      if (i) return void (n.id = i[1]);
      const o = s.match(av);
      o && n.setAttribute(o[1], o[2] ?? "");
    })(r, e), r.className = Qr, r.setAttribute("aria-label", "Open Angie"), r.setAttribute("aria-expanded", "false"), r.type = "button", r.innerHTML = `<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 16 16" fill="none">
	<path d="M15.0998 8.00414L14.622 8.18282C13.4991 8.60516 12.8142 9.50669 12.4001 10.6519L12.2249 11.1392L12.0497 10.6519C11.6356 9.50669 10.9109 8.60516 9.78801 8.18282L9.31018 8.00414L9.78801 7.82546C10.9109 7.40312 11.6356 6.50159 12.0497 5.3564L12.2249 4.86909L12.4001 5.3564C12.8142 6.50159 13.4991 7.40312 14.622 7.82546L15.0998 8.00414Z" fill="white"/>
	<path d="M2 8.42721C5.5608 8.42721 8.44479 11.3685 8.44479 15" stroke="white" stroke-width="2.05092" stroke-miterlimit="10"/>
	<path d="M2 7.57275C5.5608 7.57275 8.44479 4.6315 8.44479 0.999991" stroke="white" stroke-width="2.05092" stroke-miterlimit="10"/>
</svg>`, document.body.appendChild(r);
  })(t.toggleButtonSelector), ((e) => {
    ((r) => {
      const n = yi(r.toggleButtonSelector);
      Sh(n ? { toggleButtonSelector: r.toggleButtonSelector, onClick: () => {
        const s = n.getAttribute("aria-expanded") === "true";
        rr({ containerId: r.containerId, toggleButtonSelector: r.toggleButtonSelector, isOpen: !s }), s && r.onClose?.();
      } } : { toggleButtonSelector: r.toggleButtonSelector, onClick: () => {
        const s = yi(r.toggleButtonSelector);
        if (!s) return;
        const i = s.getAttribute("aria-expanded") === "true";
        rr({ containerId: r.containerId, toggleButtonSelector: r.toggleButtonSelector, isOpen: !i }), i && r.onClose?.();
      } });
    })(e), cv(e), window.toggleAngieSidebar = (r) => {
      kh(e, { force: r });
    };
  })({ containerId: t.containerId, iframeOrigin: t.iframeOrigin, onClose: t.onClose, toggleButtonSelector: t.toggleButtonSelector });
};
let wi = !1;
const dv = { initShell: ({ config: t }) => {
  ((e, r) => {
    const n = e.chatToggleButton.enabled ? e.chatToggleButton.selector : void 0;
    yh({ onToggle: (s) => {
      n && pa(n, s), !s && r.onClose && r.onClose();
    } }), ((s) => {
      if (s !== "wordpress" || typeof document > "u") return;
      const i = "angie-sidebar-wordpress-styles";
      if (document.getElementById(i) || (wi = !1), wi) return;
      const o = document.createElement("style");
      o.id = i, o.textContent = `body.admin-bar {
    --angie-sidebar-z-index: 99999;
}

#angie-body-top-padding {
    height: 0;
    transition: height 0.3s ease-in-out;
}

body.angie-sidebar-transitioning #wpadminbar {
    transition: var(--angie-sidebar-transition) !important;
}

@media (min-width: 768px) {
    body.angie-sidebar-active #angie-body-top-padding {
        width: 100%;
        height: 0;
    }

    body.angie-sidebar-active #wpadminbar {
        inset-inline-start: var(--angie-sidebar-width) !important;
        inset-inline-end: 0 !important;
        width: calc(100% - var(--angie-sidebar-width)) !important;
        margin-top: 0;
    }
}

@media (max-width: 768px) {
    body.angie-sidebar-active #wpadminbar {
        inset-inline-start: 0 !important;
        inset-inline-end: 0 !important;
        width: 100% !important;
    }
}

body:not(.wp-admin) #angie-sidebar-container {
    margin-top: 3px;
}
`, (document.head || document.getElementsByTagName("head")[0]).appendChild(o), wi = !0;
    })(e.styleTheme), n && Sh({ toggleButtonSelector: n, onClick: (s) => {
      s.preventDefault(), window.toggleAngieSidebar?.();
    } });
  })(t.container, t.callbacks);
}, beforeOpenIframe: ({ config: t }) => {
  var e;
  (e = t.container).chatToggleButton.enabled && (e.persistOpenState && _h() === an || uo(cn));
}, afterOpenIframe: ({ config: t }) => {
  var e;
  (e = t.container).persistOpenState && ev(e.chatToggleButton.enabled ? cn : an), e.resizable && tv();
} }, hv = { initShell: ({ config: t }) => {
  const { chatToggleButton: e } = t.container;
  lv({ containerId: t.container.id, iframeOrigin: t.iframe.origin, onClose: t.callbacks.onClose, toggleButtonSelector: e.selector, injectToggleButton: e.enabled });
} }, fv = { [vh]: dv, [un]: hv }, pv = { layout: un, styleTheme: "", persistOpenState: !1, resizable: !1, chatToggleButtonEnabled: !0 }, pc = { closeButton: "collapse" }, mv = (t, e) => t === un ? { closeButton: "close", ...e } : e ? { ...pc, ...e } : pc, gv = async (t) => {
  mh();
  const e = { browserUiTheme: typeof window < "u" && typeof window.matchMedia == "function" ? window.matchMedia("(prefers-color-scheme: dark)").matches ? "dark" : "light" : ar.iframe.uiTheme, isInIframe: typeof window < "u" && window !== window.top, isRTL: typeof document < "u" && document.documentElement.dir === "rtl" }, r = ((u, l) => {
    const h = u.boot ?? {}, p = u.container ?? {}, g = u.iframe ?? {}, S = u.callbacks ?? {}, k = p.layout ?? ar.container.layout, y = /* @__PURE__ */ ((m) => m === un ? pv : bh)(k), b = p.chatToggleButton?.enabled ?? y.chatToggleButtonEnabled;
    return { host: { appId: u.host.appId, aiContext: u.host.aiContext, website: u.host.website }, boot: { allowInIframe: h.allowInIframe ?? ar.boot.allowInIframe }, container: { id: p.id?.trim() || ua, layout: k, styleTheme: p.styleTheme ?? y.styleTheme, persistOpenState: p.persistOpenState ?? y.persistOpenState, resizable: p.resizable ?? y.resizable, chatToggleButton: { enabled: b, selector: p.chatToggleButton?.selector?.trim() || ar.container.chatToggleButtonSelector } }, iframe: { origin: g.origin?.trim() || ar.iframe.origin, path: g.path?.trim() || ar.iframe.path, uiTheme: g.uiTheme ?? l.browserUiTheme, isRTL: g.isRTL ?? l.isRTL }, callbacks: { onClose: S.onClose, getExternalHeaders: S.getExternalHeaders }, widgetConfig: mv(k, u.widgetConfig) };
  })(t, e);
  if (!((u, l) => !(!u.boot.allowInIframe && l.isInIframe))(r, e)) return;
  var n;
  n = { iframeOrigin: r.iframe.origin, host: r.host, getExternalHeaders: r.callbacks.getExternalHeaders }, hr = n, uc || (uc = !0, window.addEventListener("message", (u) => {
    sv(u);
  })), ge.containerId = r.container.id, ((u, l) => {
    if (document.getElementById(u)) return;
    const h = document.createElement("div");
    h.id = u, h.dir = l ? "rtl" : "ltr";
    const p = document.createElement("div");
    p.id = "angie-sidebar-loading", p.setAttribute("aria-live", "polite"), p.className = "angie-sr-only", h.appendChild(p), document.body.appendChild(h);
  })(r.container.id, e.isRTL);
  const s = fv[r.container.layout], i = { config: r, env: e };
  s.initShell(i), s.beforeOpenIframe?.(i);
  const o = { aiContext: (a = r.host).aiContext, appId: a.appId, configVersion: 2, telemetry: { screenPath: window.location.pathname }, website: { docTitle: document.title, homeUrl: window.location.origin, name: document.title, platform: "frontend", siteLang: document.documentElement.lang, tagline: "", ...a.website } };
  var a, c;
  await (async (u) => {
    await wh({ isRTL: u.iframe.isRTL, origin: u.iframe.origin, path: u.iframe.path, uiTheme: u.iframe.uiTheme, embeddedConfig: u.embeddedConfig }), u.container.layout === un && u.container.chatToggleButton.enabled ? rr({ containerId: u.container.id, toggleButtonSelector: u.container.chatToggleButton.selector, isOpen: !1 }) : (ge.iframe && ti(ge.iframe, !1), u.container.chatToggleButton.enabled && pa(u.container.chatToggleButton.selector, !1));
  })({ container: r.container, iframe: r.iframe, embeddedConfig: o }), s.afterOpenIframe?.(i), Tr({ payload: o, type: "sdk-embedded-config" }), r.widgetConfig && (c = r.widgetConfig, Tr({ payload: c, type: "sdk-widget-config" }));
}, mc = "angie-prompt", _v = { origin: "https://angie.elementor.com", uiTheme: "light", isRTL: !1, containerId: ua, skipDefaultCss: !1, path: "angie/wp-admin" };
class yv {
  angieDetector;
  clientManager;
  logger;
  registrationQueue;
  isInitialized = !1;
  instanceId;
  sidebarV2BootPromise = null;
  constructor() {
    this.instanceId = Math.random().toString(36).substring(2, 8), this.logger = $t({ instanceId: this.instanceId }), this.logger.log("Constructor called - initializing SDK"), this.angieDetector = new _w(), this.registrationQueue = new nv(), this.clientManager = new ww(), this.logger.log("Setting up event handlers"), this.setupAngieReadyHandler(), this.setupServerInitHandler(), this.setupReRegistrationHandler(), this.logger.log("SDK initialization complete");
  }
  async loadSidebar(e) {
    mh();
    const { widgetConfig: r, ...n } = e || {}, s = { ..._v, ...n };
    ge.containerId = s.containerId, yh({ skipDefaultCss: s.skipDefaultCss }), await wh(s), r && Tr({ type: "sdk-widget-config", payload: r }), this.setupPromptHashDetection();
  }
  loadSidebarV2(e) {
    return this.sidebarV2BootPromise = gv(e), this.sidebarV2BootPromise;
  }
  setupReRegistrationHandler() {
    window.addEventListener("message", (e) => {
      if (e.data?.type === Ie.SDK_ANGIE_REFRESH_PING) if (this.logger.log("Angie refresh ping received"), this.registrationQueue.resetAllToPending()) {
        const r = this.registrationQueue.getPending().length;
        this.logger.log(`Successfully reset ${r} registrations, processing queue`), this.handleAngieReady();
      } else this.logger.log("Skipping queue reset - processing already in progress");
    });
  }
  setupAngieReadyHandler() {
    this.angieDetector.waitForReady().then((e) => {
      e.isReady ? this.handleAngieReady() : this.logger.warn("Angie not detected - servers will remain queued");
    }).catch((e) => {
      this.logger.error("Error waiting for Angie:", e);
    });
  }
  async handleAngieReady() {
    this.logger.log("Angie is ready, processing queued registrations");
    try {
      await this.registrationQueue.processQueue(async (e) => {
        this.logger.log(`processQueue callback called for "${e.config.name}"`), await this.processRegistration(e);
      }), this.isInitialized = !0, this.logger.log("Initialization complete");
    } catch (e) {
      this.logger.error("Error processing registration queue:", e);
    }
  }
  async processRegistration(e) {
    this.logger.log(`Processing registration for server "${e.config.name}" (ID: ${e.id})`);
    try {
      this.logger.log(`Calling clientManager.requestClientCreation for "${e.config.name}"`);
      const r = { ...e, instanceId: this.instanceId };
      await this.clientManager.requestClientCreation(r), this.logger.log(`Successfully registered server "${e.config.name}"`);
    } catch (r) {
      throw this.logger.error(`Failed to register server "${e.config.name}":`, r), r;
    }
  }
  registerLocalServer(e) {
    return e.type = gr.LOCAL, e.transport = Ps.POST_MESSAGE, this.registerServer(e);
  }
  registerRemoteServer(e) {
    return e.type = gr.REMOTE, this.registerServer(e);
  }
  isLocalServerConfig(e) {
    return e.type === gr.LOCAL || !e.type && "server" in e;
  }
  isRemoteServerConfig(e) {
    return e.type === gr.REMOTE && "url" in e;
  }
  async registerServer(e) {
    if (!e.type) return this.logger.warn("For a local server, please use registerLocalServer instead of registerServer"), void this.registerLocalServer(e);
    if (this.logger.log(`registerServer called for "${e.name}"`), !e.name) throw new Error("Server name is required");
    if (!e.description) throw new Error("Server description is required");
    if (this.isLocalServerConfig(e) && !e.server) throw new Error("Server instance is required for local servers");
    this.logger.log(`Registering server "${e.name}"`);
    const r = this.registrationQueue.add(e);
    if (this.logger.log(`Added registration to queue: ${r.id}`), this.angieDetector.isReady()) try {
      await this.processRegistration(r), this.registrationQueue.updateStatus(r.id, "registered"), this.logger.log(`Server "${e.name}" registered successfully`);
    } catch (n) {
      const s = n instanceof Error ? n.message : String(n);
      throw this.registrationQueue.updateStatus(r.id, "failed", s), n;
    }
    else this.logger.log(`Server "${e.name}" queued until Angie is ready`);
  }
  getRegistrations() {
    return this.registrationQueue.getAll();
  }
  getPendingRegistrations() {
    return this.registrationQueue.getPending();
  }
  isAngieReady() {
    return this.angieDetector.isReady();
  }
  isReady() {
    return this.isInitialized;
  }
  async waitForReady() {
    if (this.sidebarV2BootPromise) await this.sidebarV2BootPromise;
    else for (; !ge.iframe; ) await new Promise((e) => setTimeout(e, 100));
    if (!(await this.angieDetector.waitForReady()).isReady) throw new Error("Angie is not available");
    for (; !this.isInitialized; ) await new Promise((e) => setTimeout(e, 100));
  }
  async triggerAngie(e) {
    if (!this.isAngieReady()) throw new Error("Angie is not ready. Please wait for Angie to be available before triggering.");
    const r = this.generateRequestId(), n = e.options?.timeout || 3e4;
    return new Promise((s, i) => {
      const o = setTimeout(() => {
        i(new Error("Angie trigger request timed out"));
      }, n), a = (u) => {
        u.data?.type === Ie.SDK_TRIGGER_ANGIE_RESPONSE && u.data?.payload?.requestId === r && (clearTimeout(o), window.removeEventListener("message", a), s(u.data.payload));
      };
      window.addEventListener("message", a);
      const c = { type: Ie.SDK_TRIGGER_ANGIE, payload: { requestId: r, prompt: e.prompt, options: e.options, context: { pageUrl: window.location.href, pageTitle: document.title, ...e.context } }, timestamp: Date.now() };
      this.logger.log(`Triggering Angie with prompt (Request ID: ${r})`), window.postMessage(c, window.location.origin);
    });
  }
  destroy() {
    this.registrationQueue.clear(), this.logger.log("SDK destroyed");
  }
  setupServerInitHandler() {
    window.addEventListener("message", (e) => {
      e.data?.type === Ie.SDK_REQUEST_INIT_SERVER && (this.logger.log("Server init request received"), this.handleServerInitRequest(e));
    });
  }
  handleServerInitRequest(e) {
    const { clientId: r, serverId: n, instanceId: s } = e.data.payload || {};
    if (r && n) if (this.logger.log(`Server init request received - Request instanceId: ${s}, This instanceId: ${this.instanceId}`), s && s !== this.instanceId) this.logger.log(`Ignoring server init request for different instance. Request instanceId: ${s}, this instanceId: ${this.instanceId}`);
    else {
      this.logger.log(`Handling server init request for clientId: ${r}, serverId: ${n}`);
      try {
        const i = this.registrationQueue.getAll().find((u) => u.id === n);
        if (!i) return void this.logger.error(`No registration found for serverId: ${n}`);
        if ("type" in i.config && i.config.type === "remote") return void this.logger.log("Remote server registration detected; skipping local connect");
        const o = e.ports[0];
        if (!o) return void this.logger.error("No port provided in server init request");
        const a = i.config.server;
        this.migrateInstructionsCompat(a);
        const c = new yw(o);
        a.connect(c), this.logger.log(`Server "${i.config.name}" initialized successfully`);
      } catch (i) {
        this.logger.error(`Error initializing server for clientId ${r}:`, i);
      }
    }
    else this.logger.error("Invalid server init request - missing clientId or serverId");
  }
  migrateInstructionsCompat(e) {
    try {
      const r = "server" in e && e.server ? e.server : e, n = r._serverInfo, s = r._instructions;
      n?.instructions && !s && (r._instructions = n.instructions, this.logger.log("Migrated instructions from serverInfo to serverOptions (backward compat)"));
    } catch {
    }
  }
  generateRequestId() {
    return `${this.instanceId}-${Date.now()}-${Math.random().toString(36).substring(2, 8)}`;
  }
  parseHashParams(e) {
    const r = e.startsWith("#") ? e.substring(1) : e;
    return new URLSearchParams(r);
  }
  async handlePromptHash() {
    const e = window.location.hash;
    if (e.includes(`${mc}=`)) try {
      const r = this.parseHashParams(e), n = r.get(mc) || "";
      if (!n) return void this.logger.warn("Empty prompt detected in hash");
      const s = r.get("angie-new-chat") === "true";
      this.logger.log("Detected prompt in hash:", { prompt: n, newChat: s }), await this.waitForReady();
      const i = await this.triggerAngie({ prompt: n, context: { source: "hash-parameter", pageUrl: window.location.href, timestamp: (/* @__PURE__ */ new Date()).toISOString() }, options: { newChat: s } });
      this.logger.log("Triggered successfully from hash:", i), window.location.hash = "";
    } catch (r) {
      this.logger.error("Failed to trigger from hash:", r);
    }
  }
  setupPromptHashDetection() {
    this.handlePromptHash(), window.addEventListener("hashchange", () => this.handlePromptHash());
  }
}
$t("navigation");
var gc;
(function(t) {
  t.Inline = "inline", t.EndOfTurn = "end-of-turn";
})(gc || (gc = {}));
var _e;
(function(t) {
  t.assertEqual = (s) => {
  };
  function e(s) {
  }
  t.assertIs = e;
  function r(s) {
    throw new Error();
  }
  t.assertNever = r, t.arrayToEnum = (s) => {
    const i = {};
    for (const o of s)
      i[o] = o;
    return i;
  }, t.getValidEnumValues = (s) => {
    const i = t.objectKeys(s).filter((a) => typeof s[s[a]] != "number"), o = {};
    for (const a of i)
      o[a] = s[a];
    return t.objectValues(o);
  }, t.objectValues = (s) => t.objectKeys(s).map(function(i) {
    return s[i];
  }), t.objectKeys = typeof Object.keys == "function" ? (s) => Object.keys(s) : (s) => {
    const i = [];
    for (const o in s)
      Object.prototype.hasOwnProperty.call(s, o) && i.push(o);
    return i;
  }, t.find = (s, i) => {
    for (const o of s)
      if (i(o))
        return o;
  }, t.isInteger = typeof Number.isInteger == "function" ? (s) => Number.isInteger(s) : (s) => typeof s == "number" && Number.isFinite(s) && Math.floor(s) === s;
  function n(s, i = " | ") {
    return s.map((o) => typeof o == "string" ? `'${o}'` : o).join(i);
  }
  t.joinValues = n, t.jsonStringifyReplacer = (s, i) => typeof i == "bigint" ? i.toString() : i;
})(_e || (_e = {}));
var _c;
(function(t) {
  t.mergeShapes = (e, r) => ({
    ...e,
    ...r
    // second overwrites first
  });
})(_c || (_c = {}));
const Y = _e.arrayToEnum([
  "string",
  "nan",
  "number",
  "integer",
  "float",
  "boolean",
  "date",
  "bigint",
  "symbol",
  "function",
  "undefined",
  "null",
  "array",
  "object",
  "unknown",
  "promise",
  "void",
  "never",
  "map",
  "set"
]), Ut = (t) => {
  switch (typeof t) {
    case "undefined":
      return Y.undefined;
    case "string":
      return Y.string;
    case "number":
      return Number.isNaN(t) ? Y.nan : Y.number;
    case "boolean":
      return Y.boolean;
    case "function":
      return Y.function;
    case "bigint":
      return Y.bigint;
    case "symbol":
      return Y.symbol;
    case "object":
      return Array.isArray(t) ? Y.array : t === null ? Y.null : t.then && typeof t.then == "function" && t.catch && typeof t.catch == "function" ? Y.promise : typeof Map < "u" && t instanceof Map ? Y.map : typeof Set < "u" && t instanceof Set ? Y.set : typeof Date < "u" && t instanceof Date ? Y.date : Y.object;
    default:
      return Y.unknown;
  }
}, H = _e.arrayToEnum([
  "invalid_type",
  "invalid_literal",
  "custom",
  "invalid_union",
  "invalid_union_discriminator",
  "invalid_enum_value",
  "unrecognized_keys",
  "invalid_arguments",
  "invalid_return_type",
  "invalid_date",
  "invalid_string",
  "too_small",
  "too_big",
  "invalid_intersection_types",
  "not_multiple_of",
  "not_finite"
]);
class At extends Error {
  get errors() {
    return this.issues;
  }
  constructor(e) {
    super(), this.issues = [], this.addIssue = (n) => {
      this.issues = [...this.issues, n];
    }, this.addIssues = (n = []) => {
      this.issues = [...this.issues, ...n];
    };
    const r = new.target.prototype;
    Object.setPrototypeOf ? Object.setPrototypeOf(this, r) : this.__proto__ = r, this.name = "ZodError", this.issues = e;
  }
  format(e) {
    const r = e || function(i) {
      return i.message;
    }, n = { _errors: [] }, s = (i) => {
      for (const o of i.issues)
        if (o.code === "invalid_union")
          o.unionErrors.map(s);
        else if (o.code === "invalid_return_type")
          s(o.returnTypeError);
        else if (o.code === "invalid_arguments")
          s(o.argumentsError);
        else if (o.path.length === 0)
          n._errors.push(r(o));
        else {
          let a = n, c = 0;
          for (; c < o.path.length; ) {
            const u = o.path[c];
            c === o.path.length - 1 ? (a[u] = a[u] || { _errors: [] }, a[u]._errors.push(r(o))) : a[u] = a[u] || { _errors: [] }, a = a[u], c++;
          }
        }
    };
    return s(this), n;
  }
  static assert(e) {
    if (!(e instanceof At))
      throw new Error(`Not a ZodError: ${e}`);
  }
  toString() {
    return this.message;
  }
  get message() {
    return JSON.stringify(this.issues, _e.jsonStringifyReplacer, 2);
  }
  get isEmpty() {
    return this.issues.length === 0;
  }
  flatten(e = (r) => r.message) {
    const r = /* @__PURE__ */ Object.create(null), n = [];
    for (const s of this.issues)
      if (s.path.length > 0) {
        const i = s.path[0];
        r[i] = r[i] || [], r[i].push(e(s));
      } else
        n.push(e(s));
    return { formErrors: n, fieldErrors: r };
  }
  get formErrors() {
    return this.flatten();
  }
}
At.create = (t) => new At(t);
const ho = (t, e) => {
  let r;
  switch (t.code) {
    case H.invalid_type:
      t.received === Y.undefined ? r = "Required" : r = `Expected ${t.expected}, received ${t.received}`;
      break;
    case H.invalid_literal:
      r = `Invalid literal value, expected ${JSON.stringify(t.expected, _e.jsonStringifyReplacer)}`;
      break;
    case H.unrecognized_keys:
      r = `Unrecognized key(s) in object: ${_e.joinValues(t.keys, ", ")}`;
      break;
    case H.invalid_union:
      r = "Invalid input";
      break;
    case H.invalid_union_discriminator:
      r = `Invalid discriminator value. Expected ${_e.joinValues(t.options)}`;
      break;
    case H.invalid_enum_value:
      r = `Invalid enum value. Expected ${_e.joinValues(t.options)}, received '${t.received}'`;
      break;
    case H.invalid_arguments:
      r = "Invalid function arguments";
      break;
    case H.invalid_return_type:
      r = "Invalid function return type";
      break;
    case H.invalid_date:
      r = "Invalid date";
      break;
    case H.invalid_string:
      typeof t.validation == "object" ? "includes" in t.validation ? (r = `Invalid input: must include "${t.validation.includes}"`, typeof t.validation.position == "number" && (r = `${r} at one or more positions greater than or equal to ${t.validation.position}`)) : "startsWith" in t.validation ? r = `Invalid input: must start with "${t.validation.startsWith}"` : "endsWith" in t.validation ? r = `Invalid input: must end with "${t.validation.endsWith}"` : _e.assertNever(t.validation) : t.validation !== "regex" ? r = `Invalid ${t.validation}` : r = "Invalid";
      break;
    case H.too_small:
      t.type === "array" ? r = `Array must contain ${t.exact ? "exactly" : t.inclusive ? "at least" : "more than"} ${t.minimum} element(s)` : t.type === "string" ? r = `String must contain ${t.exact ? "exactly" : t.inclusive ? "at least" : "over"} ${t.minimum} character(s)` : t.type === "number" ? r = `Number must be ${t.exact ? "exactly equal to " : t.inclusive ? "greater than or equal to " : "greater than "}${t.minimum}` : t.type === "bigint" ? r = `Number must be ${t.exact ? "exactly equal to " : t.inclusive ? "greater than or equal to " : "greater than "}${t.minimum}` : t.type === "date" ? r = `Date must be ${t.exact ? "exactly equal to " : t.inclusive ? "greater than or equal to " : "greater than "}${new Date(Number(t.minimum))}` : r = "Invalid input";
      break;
    case H.too_big:
      t.type === "array" ? r = `Array must contain ${t.exact ? "exactly" : t.inclusive ? "at most" : "less than"} ${t.maximum} element(s)` : t.type === "string" ? r = `String must contain ${t.exact ? "exactly" : t.inclusive ? "at most" : "under"} ${t.maximum} character(s)` : t.type === "number" ? r = `Number must be ${t.exact ? "exactly" : t.inclusive ? "less than or equal to" : "less than"} ${t.maximum}` : t.type === "bigint" ? r = `BigInt must be ${t.exact ? "exactly" : t.inclusive ? "less than or equal to" : "less than"} ${t.maximum}` : t.type === "date" ? r = `Date must be ${t.exact ? "exactly" : t.inclusive ? "smaller than or equal to" : "smaller than"} ${new Date(Number(t.maximum))}` : r = "Invalid input";
      break;
    case H.custom:
      r = "Invalid input";
      break;
    case H.invalid_intersection_types:
      r = "Intersection results could not be merged";
      break;
    case H.not_multiple_of:
      r = `Number must be a multiple of ${t.multipleOf}`;
      break;
    case H.not_finite:
      r = "Number must be finite";
      break;
    default:
      r = e.defaultError, _e.assertNever(t);
  }
  return { message: r };
};
let wv = ho;
function vv() {
  return wv;
}
const bv = (t) => {
  const { data: e, path: r, errorMaps: n, issueData: s } = t, i = [...r, ...s.path || []], o = {
    ...s,
    path: i
  };
  if (s.message !== void 0)
    return {
      ...s,
      path: i,
      message: s.message
    };
  let a = "";
  const c = n.filter((u) => !!u).slice().reverse();
  for (const u of c)
    a = u(o, { data: e, defaultError: a }).message;
  return {
    ...s,
    path: i,
    message: a
  };
};
function J(t, e) {
  const r = vv(), n = bv({
    issueData: e,
    data: t.data,
    path: t.path,
    errorMaps: [
      t.common.contextualErrorMap,
      // contextual error map is first priority
      t.schemaErrorMap,
      // then schema-bound map if available
      r,
      // then global override map
      r === ho ? void 0 : ho
      // then global default map
    ].filter((s) => !!s)
  });
  t.common.issues.push(n);
}
class nt {
  constructor() {
    this.value = "valid";
  }
  dirty() {
    this.value === "valid" && (this.value = "dirty");
  }
  abort() {
    this.value !== "aborted" && (this.value = "aborted");
  }
  static mergeArray(e, r) {
    const n = [];
    for (const s of r) {
      if (s.status === "aborted")
        return ie;
      s.status === "dirty" && e.dirty(), n.push(s.value);
    }
    return { status: e.value, value: n };
  }
  static async mergeObjectAsync(e, r) {
    const n = [];
    for (const s of r) {
      const i = await s.key, o = await s.value;
      n.push({
        key: i,
        value: o
      });
    }
    return nt.mergeObjectSync(e, n);
  }
  static mergeObjectSync(e, r) {
    const n = {};
    for (const s of r) {
      const { key: i, value: o } = s;
      if (i.status === "aborted" || o.status === "aborted")
        return ie;
      i.status === "dirty" && e.dirty(), o.status === "dirty" && e.dirty(), i.value !== "__proto__" && (typeof o.value < "u" || s.alwaysSet) && (n[i.value] = o.value);
    }
    return { status: e.value, value: n };
  }
}
const ie = Object.freeze({
  status: "aborted"
}), Yr = (t) => ({ status: "dirty", value: t }), dt = (t) => ({ status: "valid", value: t }), yc = (t) => t.status === "aborted", wc = (t) => t.status === "dirty", Cr = (t) => t.status === "valid", As = (t) => typeof Promise < "u" && t instanceof Promise;
var ee;
(function(t) {
  t.errToObj = (e) => typeof e == "string" ? { message: e } : e || {}, t.toString = (e) => typeof e == "string" ? e : e?.message;
})(ee || (ee = {}));
class Zt {
  constructor(e, r, n, s) {
    this._cachedPath = [], this.parent = e, this.data = r, this._path = n, this._key = s;
  }
  get path() {
    return this._cachedPath.length || (Array.isArray(this._key) ? this._cachedPath.push(...this._path, ...this._key) : this._cachedPath.push(...this._path, this._key)), this._cachedPath;
  }
}
const vc = (t, e) => {
  if (Cr(e))
    return { success: !0, data: e.value };
  if (!t.common.issues.length)
    throw new Error("Validation failed but no issues detected.");
  return {
    success: !1,
    get error() {
      if (this._error)
        return this._error;
      const r = new At(t.common.issues);
      return this._error = r, this._error;
    }
  };
};
function le(t) {
  if (!t)
    return {};
  const { errorMap: e, invalid_type_error: r, required_error: n, description: s } = t;
  if (e && (r || n))
    throw new Error(`Can't use "invalid_type_error" or "required_error" in conjunction with custom error map.`);
  return e ? { errorMap: e, description: s } : { errorMap: (o, a) => {
    const { message: c } = t;
    return o.code === "invalid_enum_value" ? { message: c ?? a.defaultError } : typeof a.data > "u" ? { message: c ?? n ?? a.defaultError } : o.code !== "invalid_type" ? { message: a.defaultError } : { message: c ?? r ?? a.defaultError };
  }, description: s };
}
class pe {
  get description() {
    return this._def.description;
  }
  _getType(e) {
    return Ut(e.data);
  }
  _getOrReturnCtx(e, r) {
    return r || {
      common: e.parent.common,
      data: e.data,
      parsedType: Ut(e.data),
      schemaErrorMap: this._def.errorMap,
      path: e.path,
      parent: e.parent
    };
  }
  _processInputParams(e) {
    return {
      status: new nt(),
      ctx: {
        common: e.parent.common,
        data: e.data,
        parsedType: Ut(e.data),
        schemaErrorMap: this._def.errorMap,
        path: e.path,
        parent: e.parent
      }
    };
  }
  _parseSync(e) {
    const r = this._parse(e);
    if (As(r))
      throw new Error("Synchronous parse encountered promise.");
    return r;
  }
  _parseAsync(e) {
    const r = this._parse(e);
    return Promise.resolve(r);
  }
  parse(e, r) {
    const n = this.safeParse(e, r);
    if (n.success)
      return n.data;
    throw n.error;
  }
  safeParse(e, r) {
    const n = {
      common: {
        issues: [],
        async: r?.async ?? !1,
        contextualErrorMap: r?.errorMap
      },
      path: r?.path || [],
      schemaErrorMap: this._def.errorMap,
      parent: null,
      data: e,
      parsedType: Ut(e)
    }, s = this._parseSync({ data: e, path: n.path, parent: n });
    return vc(n, s);
  }
  "~validate"(e) {
    const r = {
      common: {
        issues: [],
        async: !!this["~standard"].async
      },
      path: [],
      schemaErrorMap: this._def.errorMap,
      parent: null,
      data: e,
      parsedType: Ut(e)
    };
    if (!this["~standard"].async)
      try {
        const n = this._parseSync({ data: e, path: [], parent: r });
        return Cr(n) ? {
          value: n.value
        } : {
          issues: r.common.issues
        };
      } catch (n) {
        n?.message?.toLowerCase()?.includes("encountered") && (this["~standard"].async = !0), r.common = {
          issues: [],
          async: !0
        };
      }
    return this._parseAsync({ data: e, path: [], parent: r }).then((n) => Cr(n) ? {
      value: n.value
    } : {
      issues: r.common.issues
    });
  }
  async parseAsync(e, r) {
    const n = await this.safeParseAsync(e, r);
    if (n.success)
      return n.data;
    throw n.error;
  }
  async safeParseAsync(e, r) {
    const n = {
      common: {
        issues: [],
        contextualErrorMap: r?.errorMap,
        async: !0
      },
      path: r?.path || [],
      schemaErrorMap: this._def.errorMap,
      parent: null,
      data: e,
      parsedType: Ut(e)
    }, s = this._parse({ data: e, path: n.path, parent: n }), i = await (As(s) ? s : Promise.resolve(s));
    return vc(n, i);
  }
  refine(e, r) {
    const n = (s) => typeof r == "string" || typeof r > "u" ? { message: r } : typeof r == "function" ? r(s) : r;
    return this._refinement((s, i) => {
      const o = e(s), a = () => i.addIssue({
        code: H.custom,
        ...n(s)
      });
      return typeof Promise < "u" && o instanceof Promise ? o.then((c) => c ? !0 : (a(), !1)) : o ? !0 : (a(), !1);
    });
  }
  refinement(e, r) {
    return this._refinement((n, s) => e(n) ? !0 : (s.addIssue(typeof r == "function" ? r(n, s) : r), !1));
  }
  _refinement(e) {
    return new Ar({
      schema: this,
      typeName: F.ZodEffects,
      effect: { type: "refinement", refinement: e }
    });
  }
  superRefine(e) {
    return this._refinement(e);
  }
  constructor(e) {
    this.spa = this.safeParseAsync, this._def = e, this.parse = this.parse.bind(this), this.safeParse = this.safeParse.bind(this), this.parseAsync = this.parseAsync.bind(this), this.safeParseAsync = this.safeParseAsync.bind(this), this.spa = this.spa.bind(this), this.refine = this.refine.bind(this), this.refinement = this.refinement.bind(this), this.superRefine = this.superRefine.bind(this), this.optional = this.optional.bind(this), this.nullable = this.nullable.bind(this), this.nullish = this.nullish.bind(this), this.array = this.array.bind(this), this.promise = this.promise.bind(this), this.or = this.or.bind(this), this.and = this.and.bind(this), this.transform = this.transform.bind(this), this.brand = this.brand.bind(this), this.default = this.default.bind(this), this.catch = this.catch.bind(this), this.describe = this.describe.bind(this), this.pipe = this.pipe.bind(this), this.readonly = this.readonly.bind(this), this.isNullable = this.isNullable.bind(this), this.isOptional = this.isOptional.bind(this), this["~standard"] = {
      version: 1,
      vendor: "zod",
      validate: (r) => this["~validate"](r)
    };
  }
  optional() {
    return Lt.create(this, this._def);
  }
  nullable() {
    return Nr.create(this, this._def);
  }
  nullish() {
    return this.nullable().optional();
  }
  array() {
    return kt.create(this);
  }
  promise() {
    return js.create(this, this._def);
  }
  or(e) {
    return xs.create([this, e], this._def);
  }
  and(e) {
    return zs.create(this, e, this._def);
  }
  transform(e) {
    return new Ar({
      ...le(this._def),
      schema: this,
      typeName: F.ZodEffects,
      effect: { type: "transform", transform: e }
    });
  }
  default(e) {
    const r = typeof e == "function" ? e : () => e;
    return new po({
      ...le(this._def),
      innerType: this,
      defaultValue: r,
      typeName: F.ZodDefault
    });
  }
  brand() {
    return new Fv({
      typeName: F.ZodBranded,
      type: this,
      ...le(this._def)
    });
  }
  catch(e) {
    const r = typeof e == "function" ? e : () => e;
    return new mo({
      ...le(this._def),
      innerType: this,
      catchValue: r,
      typeName: F.ZodCatch
    });
  }
  describe(e) {
    const r = this.constructor;
    return new r({
      ...this._def,
      description: e
    });
  }
  pipe(e) {
    return ma.create(this, e);
  }
  readonly() {
    return go.create(this);
  }
  isOptional() {
    return this.safeParse(void 0).success;
  }
  isNullable() {
    return this.safeParse(null).success;
  }
}
const Sv = /^c[^\s-]{8,}$/i, kv = /^[0-9a-z]+$/, $v = /^[0-9A-HJKMNP-TV-Z]{26}$/i, Ev = /^[0-9a-fA-F]{8}\b-[0-9a-fA-F]{4}\b-[0-9a-fA-F]{4}\b-[0-9a-fA-F]{4}\b-[0-9a-fA-F]{12}$/i, Tv = /^[a-z0-9_-]{21}$/i, Rv = /^[A-Za-z0-9-_]+\.[A-Za-z0-9-_]+\.[A-Za-z0-9-_]*$/, Iv = /^[-+]?P(?!$)(?:(?:[-+]?\d+Y)|(?:[-+]?\d+[.,]\d+Y$))?(?:(?:[-+]?\d+M)|(?:[-+]?\d+[.,]\d+M$))?(?:(?:[-+]?\d+W)|(?:[-+]?\d+[.,]\d+W$))?(?:(?:[-+]?\d+D)|(?:[-+]?\d+[.,]\d+D$))?(?:T(?=[\d+-])(?:(?:[-+]?\d+H)|(?:[-+]?\d+[.,]\d+H$))?(?:(?:[-+]?\d+M)|(?:[-+]?\d+[.,]\d+M$))?(?:[-+]?\d+(?:[.,]\d+)?S)?)??$/, Pv = /^(?!\.)(?!.*\.\.)([A-Z0-9_'+\-\.]*)[A-Z0-9_+-]@([A-Z0-9][A-Z0-9\-]*\.)+[A-Z]{2,}$/i, Cv = "^(\\p{Extended_Pictographic}|\\p{Emoji_Component})+$";
let vi;
const Ov = /^(?:(?:25[0-5]|2[0-4][0-9]|1[0-9][0-9]|[1-9][0-9]|[0-9])\.){3}(?:25[0-5]|2[0-4][0-9]|1[0-9][0-9]|[1-9][0-9]|[0-9])$/, Av = /^(?:(?:25[0-5]|2[0-4][0-9]|1[0-9][0-9]|[1-9][0-9]|[0-9])\.){3}(?:25[0-5]|2[0-4][0-9]|1[0-9][0-9]|[1-9][0-9]|[0-9])\/(3[0-2]|[12]?[0-9])$/, Nv = /^(([0-9a-fA-F]{1,4}:){7,7}[0-9a-fA-F]{1,4}|([0-9a-fA-F]{1,4}:){1,7}:|([0-9a-fA-F]{1,4}:){1,6}:[0-9a-fA-F]{1,4}|([0-9a-fA-F]{1,4}:){1,5}(:[0-9a-fA-F]{1,4}){1,2}|([0-9a-fA-F]{1,4}:){1,4}(:[0-9a-fA-F]{1,4}){1,3}|([0-9a-fA-F]{1,4}:){1,3}(:[0-9a-fA-F]{1,4}){1,4}|([0-9a-fA-F]{1,4}:){1,2}(:[0-9a-fA-F]{1,4}){1,5}|[0-9a-fA-F]{1,4}:((:[0-9a-fA-F]{1,4}){1,6})|:((:[0-9a-fA-F]{1,4}){1,7}|:)|fe80:(:[0-9a-fA-F]{0,4}){0,4}%[0-9a-zA-Z]{1,}|::(ffff(:0{1,4}){0,1}:){0,1}((25[0-5]|(2[0-4]|1{0,1}[0-9]){0,1}[0-9])\.){3,3}(25[0-5]|(2[0-4]|1{0,1}[0-9]){0,1}[0-9])|([0-9a-fA-F]{1,4}:){1,4}:((25[0-5]|(2[0-4]|1{0,1}[0-9]){0,1}[0-9])\.){3,3}(25[0-5]|(2[0-4]|1{0,1}[0-9]){0,1}[0-9]))$/, xv = /^(([0-9a-fA-F]{1,4}:){7,7}[0-9a-fA-F]{1,4}|([0-9a-fA-F]{1,4}:){1,7}:|([0-9a-fA-F]{1,4}:){1,6}:[0-9a-fA-F]{1,4}|([0-9a-fA-F]{1,4}:){1,5}(:[0-9a-fA-F]{1,4}){1,2}|([0-9a-fA-F]{1,4}:){1,4}(:[0-9a-fA-F]{1,4}){1,3}|([0-9a-fA-F]{1,4}:){1,3}(:[0-9a-fA-F]{1,4}){1,4}|([0-9a-fA-F]{1,4}:){1,2}(:[0-9a-fA-F]{1,4}){1,5}|[0-9a-fA-F]{1,4}:((:[0-9a-fA-F]{1,4}){1,6})|:((:[0-9a-fA-F]{1,4}){1,7}|:)|fe80:(:[0-9a-fA-F]{0,4}){0,4}%[0-9a-zA-Z]{1,}|::(ffff(:0{1,4}){0,1}:){0,1}((25[0-5]|(2[0-4]|1{0,1}[0-9]){0,1}[0-9])\.){3,3}(25[0-5]|(2[0-4]|1{0,1}[0-9]){0,1}[0-9])|([0-9a-fA-F]{1,4}:){1,4}:((25[0-5]|(2[0-4]|1{0,1}[0-9]){0,1}[0-9])\.){3,3}(25[0-5]|(2[0-4]|1{0,1}[0-9]){0,1}[0-9]))\/(12[0-8]|1[01][0-9]|[1-9]?[0-9])$/, zv = /^([0-9a-zA-Z+/]{4})*(([0-9a-zA-Z+/]{2}==)|([0-9a-zA-Z+/]{3}=))?$/, jv = /^([0-9a-zA-Z-_]{4})*(([0-9a-zA-Z-_]{2}(==)?)|([0-9a-zA-Z-_]{3}(=)?))?$/, $h = "((\\d\\d[2468][048]|\\d\\d[13579][26]|\\d\\d0[48]|[02468][048]00|[13579][26]00)-02-29|\\d{4}-((0[13578]|1[02])-(0[1-9]|[12]\\d|3[01])|(0[469]|11)-(0[1-9]|[12]\\d|30)|(02)-(0[1-9]|1\\d|2[0-8])))", Mv = new RegExp(`^${$h}$`);
function Eh(t) {
  let e = "[0-5]\\d";
  t.precision ? e = `${e}\\.\\d{${t.precision}}` : t.precision == null && (e = `${e}(\\.\\d+)?`);
  const r = t.precision ? "+" : "?";
  return `([01]\\d|2[0-3]):[0-5]\\d(:${e})${r}`;
}
function qv(t) {
  return new RegExp(`^${Eh(t)}$`);
}
function Uv(t) {
  let e = `${$h}T${Eh(t)}`;
  const r = [];
  return r.push(t.local ? "Z?" : "Z"), t.offset && r.push("([+-]\\d{2}:?\\d{2})"), e = `${e}(${r.join("|")})`, new RegExp(`^${e}$`);
}
function Dv(t, e) {
  return !!((e === "v4" || !e) && Ov.test(t) || (e === "v6" || !e) && Nv.test(t));
}
function Lv(t, e) {
  if (!Rv.test(t))
    return !1;
  try {
    const [r] = t.split(".");
    if (!r)
      return !1;
    const n = r.replace(/-/g, "+").replace(/_/g, "/").padEnd(r.length + (4 - r.length % 4) % 4, "="), s = JSON.parse(atob(n));
    return !(typeof s != "object" || s === null || "typ" in s && s?.typ !== "JWT" || !s.alg || e && s.alg !== e);
  } catch {
    return !1;
  }
}
function Zv(t, e) {
  return !!((e === "v4" || !e) && Av.test(t) || (e === "v6" || !e) && xv.test(t));
}
class er extends pe {
  _parse(e) {
    if (this._def.coerce && (e.data = String(e.data)), this._getType(e) !== Y.string) {
      const i = this._getOrReturnCtx(e);
      return J(i, {
        code: H.invalid_type,
        expected: Y.string,
        received: i.parsedType
      }), ie;
    }
    const n = new nt();
    let s;
    for (const i of this._def.checks)
      if (i.kind === "min")
        e.data.length < i.value && (s = this._getOrReturnCtx(e, s), J(s, {
          code: H.too_small,
          minimum: i.value,
          type: "string",
          inclusive: !0,
          exact: !1,
          message: i.message
        }), n.dirty());
      else if (i.kind === "max")
        e.data.length > i.value && (s = this._getOrReturnCtx(e, s), J(s, {
          code: H.too_big,
          maximum: i.value,
          type: "string",
          inclusive: !0,
          exact: !1,
          message: i.message
        }), n.dirty());
      else if (i.kind === "length") {
        const o = e.data.length > i.value, a = e.data.length < i.value;
        (o || a) && (s = this._getOrReturnCtx(e, s), o ? J(s, {
          code: H.too_big,
          maximum: i.value,
          type: "string",
          inclusive: !0,
          exact: !0,
          message: i.message
        }) : a && J(s, {
          code: H.too_small,
          minimum: i.value,
          type: "string",
          inclusive: !0,
          exact: !0,
          message: i.message
        }), n.dirty());
      } else if (i.kind === "email")
        Pv.test(e.data) || (s = this._getOrReturnCtx(e, s), J(s, {
          validation: "email",
          code: H.invalid_string,
          message: i.message
        }), n.dirty());
      else if (i.kind === "emoji")
        vi || (vi = new RegExp(Cv, "u")), vi.test(e.data) || (s = this._getOrReturnCtx(e, s), J(s, {
          validation: "emoji",
          code: H.invalid_string,
          message: i.message
        }), n.dirty());
      else if (i.kind === "uuid")
        Ev.test(e.data) || (s = this._getOrReturnCtx(e, s), J(s, {
          validation: "uuid",
          code: H.invalid_string,
          message: i.message
        }), n.dirty());
      else if (i.kind === "nanoid")
        Tv.test(e.data) || (s = this._getOrReturnCtx(e, s), J(s, {
          validation: "nanoid",
          code: H.invalid_string,
          message: i.message
        }), n.dirty());
      else if (i.kind === "cuid")
        Sv.test(e.data) || (s = this._getOrReturnCtx(e, s), J(s, {
          validation: "cuid",
          code: H.invalid_string,
          message: i.message
        }), n.dirty());
      else if (i.kind === "cuid2")
        kv.test(e.data) || (s = this._getOrReturnCtx(e, s), J(s, {
          validation: "cuid2",
          code: H.invalid_string,
          message: i.message
        }), n.dirty());
      else if (i.kind === "ulid")
        $v.test(e.data) || (s = this._getOrReturnCtx(e, s), J(s, {
          validation: "ulid",
          code: H.invalid_string,
          message: i.message
        }), n.dirty());
      else if (i.kind === "url")
        try {
          new URL(e.data);
        } catch {
          s = this._getOrReturnCtx(e, s), J(s, {
            validation: "url",
            code: H.invalid_string,
            message: i.message
          }), n.dirty();
        }
      else i.kind === "regex" ? (i.regex.lastIndex = 0, i.regex.test(e.data) || (s = this._getOrReturnCtx(e, s), J(s, {
        validation: "regex",
        code: H.invalid_string,
        message: i.message
      }), n.dirty())) : i.kind === "trim" ? e.data = e.data.trim() : i.kind === "includes" ? e.data.includes(i.value, i.position) || (s = this._getOrReturnCtx(e, s), J(s, {
        code: H.invalid_string,
        validation: { includes: i.value, position: i.position },
        message: i.message
      }), n.dirty()) : i.kind === "toLowerCase" ? e.data = e.data.toLowerCase() : i.kind === "toUpperCase" ? e.data = e.data.toUpperCase() : i.kind === "startsWith" ? e.data.startsWith(i.value) || (s = this._getOrReturnCtx(e, s), J(s, {
        code: H.invalid_string,
        validation: { startsWith: i.value },
        message: i.message
      }), n.dirty()) : i.kind === "endsWith" ? e.data.endsWith(i.value) || (s = this._getOrReturnCtx(e, s), J(s, {
        code: H.invalid_string,
        validation: { endsWith: i.value },
        message: i.message
      }), n.dirty()) : i.kind === "datetime" ? Uv(i).test(e.data) || (s = this._getOrReturnCtx(e, s), J(s, {
        code: H.invalid_string,
        validation: "datetime",
        message: i.message
      }), n.dirty()) : i.kind === "date" ? Mv.test(e.data) || (s = this._getOrReturnCtx(e, s), J(s, {
        code: H.invalid_string,
        validation: "date",
        message: i.message
      }), n.dirty()) : i.kind === "time" ? qv(i).test(e.data) || (s = this._getOrReturnCtx(e, s), J(s, {
        code: H.invalid_string,
        validation: "time",
        message: i.message
      }), n.dirty()) : i.kind === "duration" ? Iv.test(e.data) || (s = this._getOrReturnCtx(e, s), J(s, {
        validation: "duration",
        code: H.invalid_string,
        message: i.message
      }), n.dirty()) : i.kind === "ip" ? Dv(e.data, i.version) || (s = this._getOrReturnCtx(e, s), J(s, {
        validation: "ip",
        code: H.invalid_string,
        message: i.message
      }), n.dirty()) : i.kind === "jwt" ? Lv(e.data, i.alg) || (s = this._getOrReturnCtx(e, s), J(s, {
        validation: "jwt",
        code: H.invalid_string,
        message: i.message
      }), n.dirty()) : i.kind === "cidr" ? Zv(e.data, i.version) || (s = this._getOrReturnCtx(e, s), J(s, {
        validation: "cidr",
        code: H.invalid_string,
        message: i.message
      }), n.dirty()) : i.kind === "base64" ? zv.test(e.data) || (s = this._getOrReturnCtx(e, s), J(s, {
        validation: "base64",
        code: H.invalid_string,
        message: i.message
      }), n.dirty()) : i.kind === "base64url" ? jv.test(e.data) || (s = this._getOrReturnCtx(e, s), J(s, {
        validation: "base64url",
        code: H.invalid_string,
        message: i.message
      }), n.dirty()) : _e.assertNever(i);
    return { status: n.value, value: e.data };
  }
  _regex(e, r, n) {
    return this.refinement((s) => e.test(s), {
      validation: r,
      code: H.invalid_string,
      ...ee.errToObj(n)
    });
  }
  _addCheck(e) {
    return new er({
      ...this._def,
      checks: [...this._def.checks, e]
    });
  }
  email(e) {
    return this._addCheck({ kind: "email", ...ee.errToObj(e) });
  }
  url(e) {
    return this._addCheck({ kind: "url", ...ee.errToObj(e) });
  }
  emoji(e) {
    return this._addCheck({ kind: "emoji", ...ee.errToObj(e) });
  }
  uuid(e) {
    return this._addCheck({ kind: "uuid", ...ee.errToObj(e) });
  }
  nanoid(e) {
    return this._addCheck({ kind: "nanoid", ...ee.errToObj(e) });
  }
  cuid(e) {
    return this._addCheck({ kind: "cuid", ...ee.errToObj(e) });
  }
  cuid2(e) {
    return this._addCheck({ kind: "cuid2", ...ee.errToObj(e) });
  }
  ulid(e) {
    return this._addCheck({ kind: "ulid", ...ee.errToObj(e) });
  }
  base64(e) {
    return this._addCheck({ kind: "base64", ...ee.errToObj(e) });
  }
  base64url(e) {
    return this._addCheck({
      kind: "base64url",
      ...ee.errToObj(e)
    });
  }
  jwt(e) {
    return this._addCheck({ kind: "jwt", ...ee.errToObj(e) });
  }
  ip(e) {
    return this._addCheck({ kind: "ip", ...ee.errToObj(e) });
  }
  cidr(e) {
    return this._addCheck({ kind: "cidr", ...ee.errToObj(e) });
  }
  datetime(e) {
    return typeof e == "string" ? this._addCheck({
      kind: "datetime",
      precision: null,
      offset: !1,
      local: !1,
      message: e
    }) : this._addCheck({
      kind: "datetime",
      precision: typeof e?.precision > "u" ? null : e?.precision,
      offset: e?.offset ?? !1,
      local: e?.local ?? !1,
      ...ee.errToObj(e?.message)
    });
  }
  date(e) {
    return this._addCheck({ kind: "date", message: e });
  }
  time(e) {
    return typeof e == "string" ? this._addCheck({
      kind: "time",
      precision: null,
      message: e
    }) : this._addCheck({
      kind: "time",
      precision: typeof e?.precision > "u" ? null : e?.precision,
      ...ee.errToObj(e?.message)
    });
  }
  duration(e) {
    return this._addCheck({ kind: "duration", ...ee.errToObj(e) });
  }
  regex(e, r) {
    return this._addCheck({
      kind: "regex",
      regex: e,
      ...ee.errToObj(r)
    });
  }
  includes(e, r) {
    return this._addCheck({
      kind: "includes",
      value: e,
      position: r?.position,
      ...ee.errToObj(r?.message)
    });
  }
  startsWith(e, r) {
    return this._addCheck({
      kind: "startsWith",
      value: e,
      ...ee.errToObj(r)
    });
  }
  endsWith(e, r) {
    return this._addCheck({
      kind: "endsWith",
      value: e,
      ...ee.errToObj(r)
    });
  }
  min(e, r) {
    return this._addCheck({
      kind: "min",
      value: e,
      ...ee.errToObj(r)
    });
  }
  max(e, r) {
    return this._addCheck({
      kind: "max",
      value: e,
      ...ee.errToObj(r)
    });
  }
  length(e, r) {
    return this._addCheck({
      kind: "length",
      value: e,
      ...ee.errToObj(r)
    });
  }
  /**
   * Equivalent to `.min(1)`
   */
  nonempty(e) {
    return this.min(1, ee.errToObj(e));
  }
  trim() {
    return new er({
      ...this._def,
      checks: [...this._def.checks, { kind: "trim" }]
    });
  }
  toLowerCase() {
    return new er({
      ...this._def,
      checks: [...this._def.checks, { kind: "toLowerCase" }]
    });
  }
  toUpperCase() {
    return new er({
      ...this._def,
      checks: [...this._def.checks, { kind: "toUpperCase" }]
    });
  }
  get isDatetime() {
    return !!this._def.checks.find((e) => e.kind === "datetime");
  }
  get isDate() {
    return !!this._def.checks.find((e) => e.kind === "date");
  }
  get isTime() {
    return !!this._def.checks.find((e) => e.kind === "time");
  }
  get isDuration() {
    return !!this._def.checks.find((e) => e.kind === "duration");
  }
  get isEmail() {
    return !!this._def.checks.find((e) => e.kind === "email");
  }
  get isURL() {
    return !!this._def.checks.find((e) => e.kind === "url");
  }
  get isEmoji() {
    return !!this._def.checks.find((e) => e.kind === "emoji");
  }
  get isUUID() {
    return !!this._def.checks.find((e) => e.kind === "uuid");
  }
  get isNANOID() {
    return !!this._def.checks.find((e) => e.kind === "nanoid");
  }
  get isCUID() {
    return !!this._def.checks.find((e) => e.kind === "cuid");
  }
  get isCUID2() {
    return !!this._def.checks.find((e) => e.kind === "cuid2");
  }
  get isULID() {
    return !!this._def.checks.find((e) => e.kind === "ulid");
  }
  get isIP() {
    return !!this._def.checks.find((e) => e.kind === "ip");
  }
  get isCIDR() {
    return !!this._def.checks.find((e) => e.kind === "cidr");
  }
  get isBase64() {
    return !!this._def.checks.find((e) => e.kind === "base64");
  }
  get isBase64url() {
    return !!this._def.checks.find((e) => e.kind === "base64url");
  }
  get minLength() {
    let e = null;
    for (const r of this._def.checks)
      r.kind === "min" && (e === null || r.value > e) && (e = r.value);
    return e;
  }
  get maxLength() {
    let e = null;
    for (const r of this._def.checks)
      r.kind === "max" && (e === null || r.value < e) && (e = r.value);
    return e;
  }
}
er.create = (t) => new er({
  checks: [],
  typeName: F.ZodString,
  coerce: t?.coerce ?? !1,
  ...le(t)
});
function Hv(t, e) {
  const r = (t.toString().split(".")[1] || "").length, n = (e.toString().split(".")[1] || "").length, s = r > n ? r : n, i = Number.parseInt(t.toFixed(s).replace(".", "")), o = Number.parseInt(e.toFixed(s).replace(".", ""));
  return i % o / 10 ** s;
}
class dn extends pe {
  constructor() {
    super(...arguments), this.min = this.gte, this.max = this.lte, this.step = this.multipleOf;
  }
  _parse(e) {
    if (this._def.coerce && (e.data = Number(e.data)), this._getType(e) !== Y.number) {
      const i = this._getOrReturnCtx(e);
      return J(i, {
        code: H.invalid_type,
        expected: Y.number,
        received: i.parsedType
      }), ie;
    }
    let n;
    const s = new nt();
    for (const i of this._def.checks)
      i.kind === "int" ? _e.isInteger(e.data) || (n = this._getOrReturnCtx(e, n), J(n, {
        code: H.invalid_type,
        expected: "integer",
        received: "float",
        message: i.message
      }), s.dirty()) : i.kind === "min" ? (i.inclusive ? e.data < i.value : e.data <= i.value) && (n = this._getOrReturnCtx(e, n), J(n, {
        code: H.too_small,
        minimum: i.value,
        type: "number",
        inclusive: i.inclusive,
        exact: !1,
        message: i.message
      }), s.dirty()) : i.kind === "max" ? (i.inclusive ? e.data > i.value : e.data >= i.value) && (n = this._getOrReturnCtx(e, n), J(n, {
        code: H.too_big,
        maximum: i.value,
        type: "number",
        inclusive: i.inclusive,
        exact: !1,
        message: i.message
      }), s.dirty()) : i.kind === "multipleOf" ? Hv(e.data, i.value) !== 0 && (n = this._getOrReturnCtx(e, n), J(n, {
        code: H.not_multiple_of,
        multipleOf: i.value,
        message: i.message
      }), s.dirty()) : i.kind === "finite" ? Number.isFinite(e.data) || (n = this._getOrReturnCtx(e, n), J(n, {
        code: H.not_finite,
        message: i.message
      }), s.dirty()) : _e.assertNever(i);
    return { status: s.value, value: e.data };
  }
  gte(e, r) {
    return this.setLimit("min", e, !0, ee.toString(r));
  }
  gt(e, r) {
    return this.setLimit("min", e, !1, ee.toString(r));
  }
  lte(e, r) {
    return this.setLimit("max", e, !0, ee.toString(r));
  }
  lt(e, r) {
    return this.setLimit("max", e, !1, ee.toString(r));
  }
  setLimit(e, r, n, s) {
    return new dn({
      ...this._def,
      checks: [
        ...this._def.checks,
        {
          kind: e,
          value: r,
          inclusive: n,
          message: ee.toString(s)
        }
      ]
    });
  }
  _addCheck(e) {
    return new dn({
      ...this._def,
      checks: [...this._def.checks, e]
    });
  }
  int(e) {
    return this._addCheck({
      kind: "int",
      message: ee.toString(e)
    });
  }
  positive(e) {
    return this._addCheck({
      kind: "min",
      value: 0,
      inclusive: !1,
      message: ee.toString(e)
    });
  }
  negative(e) {
    return this._addCheck({
      kind: "max",
      value: 0,
      inclusive: !1,
      message: ee.toString(e)
    });
  }
  nonpositive(e) {
    return this._addCheck({
      kind: "max",
      value: 0,
      inclusive: !0,
      message: ee.toString(e)
    });
  }
  nonnegative(e) {
    return this._addCheck({
      kind: "min",
      value: 0,
      inclusive: !0,
      message: ee.toString(e)
    });
  }
  multipleOf(e, r) {
    return this._addCheck({
      kind: "multipleOf",
      value: e,
      message: ee.toString(r)
    });
  }
  finite(e) {
    return this._addCheck({
      kind: "finite",
      message: ee.toString(e)
    });
  }
  safe(e) {
    return this._addCheck({
      kind: "min",
      inclusive: !0,
      value: Number.MIN_SAFE_INTEGER,
      message: ee.toString(e)
    })._addCheck({
      kind: "max",
      inclusive: !0,
      value: Number.MAX_SAFE_INTEGER,
      message: ee.toString(e)
    });
  }
  get minValue() {
    let e = null;
    for (const r of this._def.checks)
      r.kind === "min" && (e === null || r.value > e) && (e = r.value);
    return e;
  }
  get maxValue() {
    let e = null;
    for (const r of this._def.checks)
      r.kind === "max" && (e === null || r.value < e) && (e = r.value);
    return e;
  }
  get isInt() {
    return !!this._def.checks.find((e) => e.kind === "int" || e.kind === "multipleOf" && _e.isInteger(e.value));
  }
  get isFinite() {
    let e = null, r = null;
    for (const n of this._def.checks) {
      if (n.kind === "finite" || n.kind === "int" || n.kind === "multipleOf")
        return !0;
      n.kind === "min" ? (r === null || n.value > r) && (r = n.value) : n.kind === "max" && (e === null || n.value < e) && (e = n.value);
    }
    return Number.isFinite(r) && Number.isFinite(e);
  }
}
dn.create = (t) => new dn({
  checks: [],
  typeName: F.ZodNumber,
  coerce: t?.coerce || !1,
  ...le(t)
});
class hn extends pe {
  constructor() {
    super(...arguments), this.min = this.gte, this.max = this.lte;
  }
  _parse(e) {
    if (this._def.coerce)
      try {
        e.data = BigInt(e.data);
      } catch {
        return this._getInvalidInput(e);
      }
    if (this._getType(e) !== Y.bigint)
      return this._getInvalidInput(e);
    let n;
    const s = new nt();
    for (const i of this._def.checks)
      i.kind === "min" ? (i.inclusive ? e.data < i.value : e.data <= i.value) && (n = this._getOrReturnCtx(e, n), J(n, {
        code: H.too_small,
        type: "bigint",
        minimum: i.value,
        inclusive: i.inclusive,
        message: i.message
      }), s.dirty()) : i.kind === "max" ? (i.inclusive ? e.data > i.value : e.data >= i.value) && (n = this._getOrReturnCtx(e, n), J(n, {
        code: H.too_big,
        type: "bigint",
        maximum: i.value,
        inclusive: i.inclusive,
        message: i.message
      }), s.dirty()) : i.kind === "multipleOf" ? e.data % i.value !== BigInt(0) && (n = this._getOrReturnCtx(e, n), J(n, {
        code: H.not_multiple_of,
        multipleOf: i.value,
        message: i.message
      }), s.dirty()) : _e.assertNever(i);
    return { status: s.value, value: e.data };
  }
  _getInvalidInput(e) {
    const r = this._getOrReturnCtx(e);
    return J(r, {
      code: H.invalid_type,
      expected: Y.bigint,
      received: r.parsedType
    }), ie;
  }
  gte(e, r) {
    return this.setLimit("min", e, !0, ee.toString(r));
  }
  gt(e, r) {
    return this.setLimit("min", e, !1, ee.toString(r));
  }
  lte(e, r) {
    return this.setLimit("max", e, !0, ee.toString(r));
  }
  lt(e, r) {
    return this.setLimit("max", e, !1, ee.toString(r));
  }
  setLimit(e, r, n, s) {
    return new hn({
      ...this._def,
      checks: [
        ...this._def.checks,
        {
          kind: e,
          value: r,
          inclusive: n,
          message: ee.toString(s)
        }
      ]
    });
  }
  _addCheck(e) {
    return new hn({
      ...this._def,
      checks: [...this._def.checks, e]
    });
  }
  positive(e) {
    return this._addCheck({
      kind: "min",
      value: BigInt(0),
      inclusive: !1,
      message: ee.toString(e)
    });
  }
  negative(e) {
    return this._addCheck({
      kind: "max",
      value: BigInt(0),
      inclusive: !1,
      message: ee.toString(e)
    });
  }
  nonpositive(e) {
    return this._addCheck({
      kind: "max",
      value: BigInt(0),
      inclusive: !0,
      message: ee.toString(e)
    });
  }
  nonnegative(e) {
    return this._addCheck({
      kind: "min",
      value: BigInt(0),
      inclusive: !0,
      message: ee.toString(e)
    });
  }
  multipleOf(e, r) {
    return this._addCheck({
      kind: "multipleOf",
      value: e,
      message: ee.toString(r)
    });
  }
  get minValue() {
    let e = null;
    for (const r of this._def.checks)
      r.kind === "min" && (e === null || r.value > e) && (e = r.value);
    return e;
  }
  get maxValue() {
    let e = null;
    for (const r of this._def.checks)
      r.kind === "max" && (e === null || r.value < e) && (e = r.value);
    return e;
  }
}
hn.create = (t) => new hn({
  checks: [],
  typeName: F.ZodBigInt,
  coerce: t?.coerce ?? !1,
  ...le(t)
});
class bc extends pe {
  _parse(e) {
    if (this._def.coerce && (e.data = !!e.data), this._getType(e) !== Y.boolean) {
      const n = this._getOrReturnCtx(e);
      return J(n, {
        code: H.invalid_type,
        expected: Y.boolean,
        received: n.parsedType
      }), ie;
    }
    return dt(e.data);
  }
}
bc.create = (t) => new bc({
  typeName: F.ZodBoolean,
  coerce: t?.coerce || !1,
  ...le(t)
});
class Ns extends pe {
  _parse(e) {
    if (this._def.coerce && (e.data = new Date(e.data)), this._getType(e) !== Y.date) {
      const i = this._getOrReturnCtx(e);
      return J(i, {
        code: H.invalid_type,
        expected: Y.date,
        received: i.parsedType
      }), ie;
    }
    if (Number.isNaN(e.data.getTime())) {
      const i = this._getOrReturnCtx(e);
      return J(i, {
        code: H.invalid_date
      }), ie;
    }
    const n = new nt();
    let s;
    for (const i of this._def.checks)
      i.kind === "min" ? e.data.getTime() < i.value && (s = this._getOrReturnCtx(e, s), J(s, {
        code: H.too_small,
        message: i.message,
        inclusive: !0,
        exact: !1,
        minimum: i.value,
        type: "date"
      }), n.dirty()) : i.kind === "max" ? e.data.getTime() > i.value && (s = this._getOrReturnCtx(e, s), J(s, {
        code: H.too_big,
        message: i.message,
        inclusive: !0,
        exact: !1,
        maximum: i.value,
        type: "date"
      }), n.dirty()) : _e.assertNever(i);
    return {
      status: n.value,
      value: new Date(e.data.getTime())
    };
  }
  _addCheck(e) {
    return new Ns({
      ...this._def,
      checks: [...this._def.checks, e]
    });
  }
  min(e, r) {
    return this._addCheck({
      kind: "min",
      value: e.getTime(),
      message: ee.toString(r)
    });
  }
  max(e, r) {
    return this._addCheck({
      kind: "max",
      value: e.getTime(),
      message: ee.toString(r)
    });
  }
  get minDate() {
    let e = null;
    for (const r of this._def.checks)
      r.kind === "min" && (e === null || r.value > e) && (e = r.value);
    return e != null ? new Date(e) : null;
  }
  get maxDate() {
    let e = null;
    for (const r of this._def.checks)
      r.kind === "max" && (e === null || r.value < e) && (e = r.value);
    return e != null ? new Date(e) : null;
  }
}
Ns.create = (t) => new Ns({
  checks: [],
  coerce: t?.coerce || !1,
  typeName: F.ZodDate,
  ...le(t)
});
class Sc extends pe {
  _parse(e) {
    if (this._getType(e) !== Y.symbol) {
      const n = this._getOrReturnCtx(e);
      return J(n, {
        code: H.invalid_type,
        expected: Y.symbol,
        received: n.parsedType
      }), ie;
    }
    return dt(e.data);
  }
}
Sc.create = (t) => new Sc({
  typeName: F.ZodSymbol,
  ...le(t)
});
class kc extends pe {
  _parse(e) {
    if (this._getType(e) !== Y.undefined) {
      const n = this._getOrReturnCtx(e);
      return J(n, {
        code: H.invalid_type,
        expected: Y.undefined,
        received: n.parsedType
      }), ie;
    }
    return dt(e.data);
  }
}
kc.create = (t) => new kc({
  typeName: F.ZodUndefined,
  ...le(t)
});
class $c extends pe {
  _parse(e) {
    if (this._getType(e) !== Y.null) {
      const n = this._getOrReturnCtx(e);
      return J(n, {
        code: H.invalid_type,
        expected: Y.null,
        received: n.parsedType
      }), ie;
    }
    return dt(e.data);
  }
}
$c.create = (t) => new $c({
  typeName: F.ZodNull,
  ...le(t)
});
class Ec extends pe {
  constructor() {
    super(...arguments), this._any = !0;
  }
  _parse(e) {
    return dt(e.data);
  }
}
Ec.create = (t) => new Ec({
  typeName: F.ZodAny,
  ...le(t)
});
class Tc extends pe {
  constructor() {
    super(...arguments), this._unknown = !0;
  }
  _parse(e) {
    return dt(e.data);
  }
}
Tc.create = (t) => new Tc({
  typeName: F.ZodUnknown,
  ...le(t)
});
class Ht extends pe {
  _parse(e) {
    const r = this._getOrReturnCtx(e);
    return J(r, {
      code: H.invalid_type,
      expected: Y.never,
      received: r.parsedType
    }), ie;
  }
}
Ht.create = (t) => new Ht({
  typeName: F.ZodNever,
  ...le(t)
});
class Rc extends pe {
  _parse(e) {
    if (this._getType(e) !== Y.undefined) {
      const n = this._getOrReturnCtx(e);
      return J(n, {
        code: H.invalid_type,
        expected: Y.void,
        received: n.parsedType
      }), ie;
    }
    return dt(e.data);
  }
}
Rc.create = (t) => new Rc({
  typeName: F.ZodVoid,
  ...le(t)
});
class kt extends pe {
  _parse(e) {
    const { ctx: r, status: n } = this._processInputParams(e), s = this._def;
    if (r.parsedType !== Y.array)
      return J(r, {
        code: H.invalid_type,
        expected: Y.array,
        received: r.parsedType
      }), ie;
    if (s.exactLength !== null) {
      const o = r.data.length > s.exactLength.value, a = r.data.length < s.exactLength.value;
      (o || a) && (J(r, {
        code: o ? H.too_big : H.too_small,
        minimum: a ? s.exactLength.value : void 0,
        maximum: o ? s.exactLength.value : void 0,
        type: "array",
        inclusive: !0,
        exact: !0,
        message: s.exactLength.message
      }), n.dirty());
    }
    if (s.minLength !== null && r.data.length < s.minLength.value && (J(r, {
      code: H.too_small,
      minimum: s.minLength.value,
      type: "array",
      inclusive: !0,
      exact: !1,
      message: s.minLength.message
    }), n.dirty()), s.maxLength !== null && r.data.length > s.maxLength.value && (J(r, {
      code: H.too_big,
      maximum: s.maxLength.value,
      type: "array",
      inclusive: !0,
      exact: !1,
      message: s.maxLength.message
    }), n.dirty()), r.common.async)
      return Promise.all([...r.data].map((o, a) => s.type._parseAsync(new Zt(r, o, r.path, a)))).then((o) => nt.mergeArray(n, o));
    const i = [...r.data].map((o, a) => s.type._parseSync(new Zt(r, o, r.path, a)));
    return nt.mergeArray(n, i);
  }
  get element() {
    return this._def.type;
  }
  min(e, r) {
    return new kt({
      ...this._def,
      minLength: { value: e, message: ee.toString(r) }
    });
  }
  max(e, r) {
    return new kt({
      ...this._def,
      maxLength: { value: e, message: ee.toString(r) }
    });
  }
  length(e, r) {
    return new kt({
      ...this._def,
      exactLength: { value: e, message: ee.toString(r) }
    });
  }
  nonempty(e) {
    return this.min(1, e);
  }
}
kt.create = (t, e) => new kt({
  type: t,
  minLength: null,
  maxLength: null,
  exactLength: null,
  typeName: F.ZodArray,
  ...le(e)
});
function fr(t) {
  if (t instanceof qe) {
    const e = {};
    for (const r in t.shape) {
      const n = t.shape[r];
      e[r] = Lt.create(fr(n));
    }
    return new qe({
      ...t._def,
      shape: () => e
    });
  } else return t instanceof kt ? new kt({
    ...t._def,
    type: fr(t.element)
  }) : t instanceof Lt ? Lt.create(fr(t.unwrap())) : t instanceof Nr ? Nr.create(fr(t.unwrap())) : t instanceof sr ? sr.create(t.items.map((e) => fr(e))) : t;
}
class qe extends pe {
  constructor() {
    super(...arguments), this._cached = null, this.nonstrict = this.passthrough, this.augment = this.extend;
  }
  _getCached() {
    if (this._cached !== null)
      return this._cached;
    const e = this._def.shape(), r = _e.objectKeys(e);
    return this._cached = { shape: e, keys: r }, this._cached;
  }
  _parse(e) {
    if (this._getType(e) !== Y.object) {
      const u = this._getOrReturnCtx(e);
      return J(u, {
        code: H.invalid_type,
        expected: Y.object,
        received: u.parsedType
      }), ie;
    }
    const { status: n, ctx: s } = this._processInputParams(e), { shape: i, keys: o } = this._getCached(), a = [];
    if (!(this._def.catchall instanceof Ht && this._def.unknownKeys === "strip"))
      for (const u in s.data)
        o.includes(u) || a.push(u);
    const c = [];
    for (const u of o) {
      const l = i[u], h = s.data[u];
      c.push({
        key: { status: "valid", value: u },
        value: l._parse(new Zt(s, h, s.path, u)),
        alwaysSet: u in s.data
      });
    }
    if (this._def.catchall instanceof Ht) {
      const u = this._def.unknownKeys;
      if (u === "passthrough")
        for (const l of a)
          c.push({
            key: { status: "valid", value: l },
            value: { status: "valid", value: s.data[l] }
          });
      else if (u === "strict")
        a.length > 0 && (J(s, {
          code: H.unrecognized_keys,
          keys: a
        }), n.dirty());
      else if (u !== "strip") throw new Error("Internal ZodObject error: invalid unknownKeys value.");
    } else {
      const u = this._def.catchall;
      for (const l of a) {
        const h = s.data[l];
        c.push({
          key: { status: "valid", value: l },
          value: u._parse(
            new Zt(s, h, s.path, l)
            //, ctx.child(key), value, getParsedType(value)
          ),
          alwaysSet: l in s.data
        });
      }
    }
    return s.common.async ? Promise.resolve().then(async () => {
      const u = [];
      for (const l of c) {
        const h = await l.key, p = await l.value;
        u.push({
          key: h,
          value: p,
          alwaysSet: l.alwaysSet
        });
      }
      return u;
    }).then((u) => nt.mergeObjectSync(n, u)) : nt.mergeObjectSync(n, c);
  }
  get shape() {
    return this._def.shape();
  }
  strict(e) {
    return ee.errToObj, new qe({
      ...this._def,
      unknownKeys: "strict",
      ...e !== void 0 ? {
        errorMap: (r, n) => {
          const s = this._def.errorMap?.(r, n).message ?? n.defaultError;
          return r.code === "unrecognized_keys" ? {
            message: ee.errToObj(e).message ?? s
          } : {
            message: s
          };
        }
      } : {}
    });
  }
  strip() {
    return new qe({
      ...this._def,
      unknownKeys: "strip"
    });
  }
  passthrough() {
    return new qe({
      ...this._def,
      unknownKeys: "passthrough"
    });
  }
  // const AugmentFactory =
  //   <Def extends ZodObjectDef>(def: Def) =>
  //   <Augmentation extends ZodRawShape>(
  //     augmentation: Augmentation
  //   ): ZodObject<
  //     extendShape<ReturnType<Def["shape"]>, Augmentation>,
  //     Def["unknownKeys"],
  //     Def["catchall"]
  //   > => {
  //     return new ZodObject({
  //       ...def,
  //       shape: () => ({
  //         ...def.shape(),
  //         ...augmentation,
  //       }),
  //     }) as any;
  //   };
  extend(e) {
    return new qe({
      ...this._def,
      shape: () => ({
        ...this._def.shape(),
        ...e
      })
    });
  }
  /**
   * Prior to zod@1.0.12 there was a bug in the
   * inferred type of merged objects. Please
   * upgrade if you are experiencing issues.
   */
  merge(e) {
    return new qe({
      unknownKeys: e._def.unknownKeys,
      catchall: e._def.catchall,
      shape: () => ({
        ...this._def.shape(),
        ...e._def.shape()
      }),
      typeName: F.ZodObject
    });
  }
  // merge<
  //   Incoming extends AnyZodObject,
  //   Augmentation extends Incoming["shape"],
  //   NewOutput extends {
  //     [k in keyof Augmentation | keyof Output]: k extends keyof Augmentation
  //       ? Augmentation[k]["_output"]
  //       : k extends keyof Output
  //       ? Output[k]
  //       : never;
  //   },
  //   NewInput extends {
  //     [k in keyof Augmentation | keyof Input]: k extends keyof Augmentation
  //       ? Augmentation[k]["_input"]
  //       : k extends keyof Input
  //       ? Input[k]
  //       : never;
  //   }
  // >(
  //   merging: Incoming
  // ): ZodObject<
  //   extendShape<T, ReturnType<Incoming["_def"]["shape"]>>,
  //   Incoming["_def"]["unknownKeys"],
  //   Incoming["_def"]["catchall"],
  //   NewOutput,
  //   NewInput
  // > {
  //   const merged: any = new ZodObject({
  //     unknownKeys: merging._def.unknownKeys,
  //     catchall: merging._def.catchall,
  //     shape: () =>
  //       objectUtil.mergeShapes(this._def.shape(), merging._def.shape()),
  //     typeName: ZodFirstPartyTypeKind.ZodObject,
  //   }) as any;
  //   return merged;
  // }
  setKey(e, r) {
    return this.augment({ [e]: r });
  }
  // merge<Incoming extends AnyZodObject>(
  //   merging: Incoming
  // ): //ZodObject<T & Incoming["_shape"], UnknownKeys, Catchall> = (merging) => {
  // ZodObject<
  //   extendShape<T, ReturnType<Incoming["_def"]["shape"]>>,
  //   Incoming["_def"]["unknownKeys"],
  //   Incoming["_def"]["catchall"]
  // > {
  //   // const mergedShape = objectUtil.mergeShapes(
  //   //   this._def.shape(),
  //   //   merging._def.shape()
  //   // );
  //   const merged: any = new ZodObject({
  //     unknownKeys: merging._def.unknownKeys,
  //     catchall: merging._def.catchall,
  //     shape: () =>
  //       objectUtil.mergeShapes(this._def.shape(), merging._def.shape()),
  //     typeName: ZodFirstPartyTypeKind.ZodObject,
  //   }) as any;
  //   return merged;
  // }
  catchall(e) {
    return new qe({
      ...this._def,
      catchall: e
    });
  }
  pick(e) {
    const r = {};
    for (const n of _e.objectKeys(e))
      e[n] && this.shape[n] && (r[n] = this.shape[n]);
    return new qe({
      ...this._def,
      shape: () => r
    });
  }
  omit(e) {
    const r = {};
    for (const n of _e.objectKeys(this.shape))
      e[n] || (r[n] = this.shape[n]);
    return new qe({
      ...this._def,
      shape: () => r
    });
  }
  /**
   * @deprecated
   */
  deepPartial() {
    return fr(this);
  }
  partial(e) {
    const r = {};
    for (const n of _e.objectKeys(this.shape)) {
      const s = this.shape[n];
      e && !e[n] ? r[n] = s : r[n] = s.optional();
    }
    return new qe({
      ...this._def,
      shape: () => r
    });
  }
  required(e) {
    const r = {};
    for (const n of _e.objectKeys(this.shape))
      if (e && !e[n])
        r[n] = this.shape[n];
      else {
        let i = this.shape[n];
        for (; i instanceof Lt; )
          i = i._def.innerType;
        r[n] = i;
      }
    return new qe({
      ...this._def,
      shape: () => r
    });
  }
  keyof() {
    return Th(_e.objectKeys(this.shape));
  }
}
qe.create = (t, e) => new qe({
  shape: () => t,
  unknownKeys: "strip",
  catchall: Ht.create(),
  typeName: F.ZodObject,
  ...le(e)
});
qe.strictCreate = (t, e) => new qe({
  shape: () => t,
  unknownKeys: "strict",
  catchall: Ht.create(),
  typeName: F.ZodObject,
  ...le(e)
});
qe.lazycreate = (t, e) => new qe({
  shape: t,
  unknownKeys: "strip",
  catchall: Ht.create(),
  typeName: F.ZodObject,
  ...le(e)
});
class xs extends pe {
  _parse(e) {
    const { ctx: r } = this._processInputParams(e), n = this._def.options;
    function s(i) {
      for (const a of i)
        if (a.result.status === "valid")
          return a.result;
      for (const a of i)
        if (a.result.status === "dirty")
          return r.common.issues.push(...a.ctx.common.issues), a.result;
      const o = i.map((a) => new At(a.ctx.common.issues));
      return J(r, {
        code: H.invalid_union,
        unionErrors: o
      }), ie;
    }
    if (r.common.async)
      return Promise.all(n.map(async (i) => {
        const o = {
          ...r,
          common: {
            ...r.common,
            issues: []
          },
          parent: null
        };
        return {
          result: await i._parseAsync({
            data: r.data,
            path: r.path,
            parent: o
          }),
          ctx: o
        };
      })).then(s);
    {
      let i;
      const o = [];
      for (const c of n) {
        const u = {
          ...r,
          common: {
            ...r.common,
            issues: []
          },
          parent: null
        }, l = c._parseSync({
          data: r.data,
          path: r.path,
          parent: u
        });
        if (l.status === "valid")
          return l;
        l.status === "dirty" && !i && (i = { result: l, ctx: u }), u.common.issues.length && o.push(u.common.issues);
      }
      if (i)
        return r.common.issues.push(...i.ctx.common.issues), i.result;
      const a = o.map((c) => new At(c));
      return J(r, {
        code: H.invalid_union,
        unionErrors: a
      }), ie;
    }
  }
  get options() {
    return this._def.options;
  }
}
xs.create = (t, e) => new xs({
  options: t,
  typeName: F.ZodUnion,
  ...le(e)
});
function fo(t, e) {
  const r = Ut(t), n = Ut(e);
  if (t === e)
    return { valid: !0, data: t };
  if (r === Y.object && n === Y.object) {
    const s = _e.objectKeys(e), i = _e.objectKeys(t).filter((a) => s.indexOf(a) !== -1), o = { ...t, ...e };
    for (const a of i) {
      const c = fo(t[a], e[a]);
      if (!c.valid)
        return { valid: !1 };
      o[a] = c.data;
    }
    return { valid: !0, data: o };
  } else if (r === Y.array && n === Y.array) {
    if (t.length !== e.length)
      return { valid: !1 };
    const s = [];
    for (let i = 0; i < t.length; i++) {
      const o = t[i], a = e[i], c = fo(o, a);
      if (!c.valid)
        return { valid: !1 };
      s.push(c.data);
    }
    return { valid: !0, data: s };
  } else return r === Y.date && n === Y.date && +t == +e ? { valid: !0, data: t } : { valid: !1 };
}
class zs extends pe {
  _parse(e) {
    const { status: r, ctx: n } = this._processInputParams(e), s = (i, o) => {
      if (yc(i) || yc(o))
        return ie;
      const a = fo(i.value, o.value);
      return a.valid ? ((wc(i) || wc(o)) && r.dirty(), { status: r.value, value: a.data }) : (J(n, {
        code: H.invalid_intersection_types
      }), ie);
    };
    return n.common.async ? Promise.all([
      this._def.left._parseAsync({
        data: n.data,
        path: n.path,
        parent: n
      }),
      this._def.right._parseAsync({
        data: n.data,
        path: n.path,
        parent: n
      })
    ]).then(([i, o]) => s(i, o)) : s(this._def.left._parseSync({
      data: n.data,
      path: n.path,
      parent: n
    }), this._def.right._parseSync({
      data: n.data,
      path: n.path,
      parent: n
    }));
  }
}
zs.create = (t, e, r) => new zs({
  left: t,
  right: e,
  typeName: F.ZodIntersection,
  ...le(r)
});
class sr extends pe {
  _parse(e) {
    const { status: r, ctx: n } = this._processInputParams(e);
    if (n.parsedType !== Y.array)
      return J(n, {
        code: H.invalid_type,
        expected: Y.array,
        received: n.parsedType
      }), ie;
    if (n.data.length < this._def.items.length)
      return J(n, {
        code: H.too_small,
        minimum: this._def.items.length,
        inclusive: !0,
        exact: !1,
        type: "array"
      }), ie;
    !this._def.rest && n.data.length > this._def.items.length && (J(n, {
      code: H.too_big,
      maximum: this._def.items.length,
      inclusive: !0,
      exact: !1,
      type: "array"
    }), r.dirty());
    const i = [...n.data].map((o, a) => {
      const c = this._def.items[a] || this._def.rest;
      return c ? c._parse(new Zt(n, o, n.path, a)) : null;
    }).filter((o) => !!o);
    return n.common.async ? Promise.all(i).then((o) => nt.mergeArray(r, o)) : nt.mergeArray(r, i);
  }
  get items() {
    return this._def.items;
  }
  rest(e) {
    return new sr({
      ...this._def,
      rest: e
    });
  }
}
sr.create = (t, e) => {
  if (!Array.isArray(t))
    throw new Error("You must pass an array of schemas to z.tuple([ ... ])");
  return new sr({
    items: t,
    typeName: F.ZodTuple,
    rest: null,
    ...le(e)
  });
};
class Ic extends pe {
  get keySchema() {
    return this._def.keyType;
  }
  get valueSchema() {
    return this._def.valueType;
  }
  _parse(e) {
    const { status: r, ctx: n } = this._processInputParams(e);
    if (n.parsedType !== Y.map)
      return J(n, {
        code: H.invalid_type,
        expected: Y.map,
        received: n.parsedType
      }), ie;
    const s = this._def.keyType, i = this._def.valueType, o = [...n.data.entries()].map(([a, c], u) => ({
      key: s._parse(new Zt(n, a, n.path, [u, "key"])),
      value: i._parse(new Zt(n, c, n.path, [u, "value"]))
    }));
    if (n.common.async) {
      const a = /* @__PURE__ */ new Map();
      return Promise.resolve().then(async () => {
        for (const c of o) {
          const u = await c.key, l = await c.value;
          if (u.status === "aborted" || l.status === "aborted")
            return ie;
          (u.status === "dirty" || l.status === "dirty") && r.dirty(), a.set(u.value, l.value);
        }
        return { status: r.value, value: a };
      });
    } else {
      const a = /* @__PURE__ */ new Map();
      for (const c of o) {
        const u = c.key, l = c.value;
        if (u.status === "aborted" || l.status === "aborted")
          return ie;
        (u.status === "dirty" || l.status === "dirty") && r.dirty(), a.set(u.value, l.value);
      }
      return { status: r.value, value: a };
    }
  }
}
Ic.create = (t, e, r) => new Ic({
  valueType: e,
  keyType: t,
  typeName: F.ZodMap,
  ...le(r)
});
class fn extends pe {
  _parse(e) {
    const { status: r, ctx: n } = this._processInputParams(e);
    if (n.parsedType !== Y.set)
      return J(n, {
        code: H.invalid_type,
        expected: Y.set,
        received: n.parsedType
      }), ie;
    const s = this._def;
    s.minSize !== null && n.data.size < s.minSize.value && (J(n, {
      code: H.too_small,
      minimum: s.minSize.value,
      type: "set",
      inclusive: !0,
      exact: !1,
      message: s.minSize.message
    }), r.dirty()), s.maxSize !== null && n.data.size > s.maxSize.value && (J(n, {
      code: H.too_big,
      maximum: s.maxSize.value,
      type: "set",
      inclusive: !0,
      exact: !1,
      message: s.maxSize.message
    }), r.dirty());
    const i = this._def.valueType;
    function o(c) {
      const u = /* @__PURE__ */ new Set();
      for (const l of c) {
        if (l.status === "aborted")
          return ie;
        l.status === "dirty" && r.dirty(), u.add(l.value);
      }
      return { status: r.value, value: u };
    }
    const a = [...n.data.values()].map((c, u) => i._parse(new Zt(n, c, n.path, u)));
    return n.common.async ? Promise.all(a).then((c) => o(c)) : o(a);
  }
  min(e, r) {
    return new fn({
      ...this._def,
      minSize: { value: e, message: ee.toString(r) }
    });
  }
  max(e, r) {
    return new fn({
      ...this._def,
      maxSize: { value: e, message: ee.toString(r) }
    });
  }
  size(e, r) {
    return this.min(e, r).max(e, r);
  }
  nonempty(e) {
    return this.min(1, e);
  }
}
fn.create = (t, e) => new fn({
  valueType: t,
  minSize: null,
  maxSize: null,
  typeName: F.ZodSet,
  ...le(e)
});
class Pc extends pe {
  get schema() {
    return this._def.getter();
  }
  _parse(e) {
    const { ctx: r } = this._processInputParams(e);
    return this._def.getter()._parse({ data: r.data, path: r.path, parent: r });
  }
}
Pc.create = (t, e) => new Pc({
  getter: t,
  typeName: F.ZodLazy,
  ...le(e)
});
class Cc extends pe {
  _parse(e) {
    if (e.data !== this._def.value) {
      const r = this._getOrReturnCtx(e);
      return J(r, {
        received: r.data,
        code: H.invalid_literal,
        expected: this._def.value
      }), ie;
    }
    return { status: "valid", value: e.data };
  }
  get value() {
    return this._def.value;
  }
}
Cc.create = (t, e) => new Cc({
  value: t,
  typeName: F.ZodLiteral,
  ...le(e)
});
function Th(t, e) {
  return new Or({
    values: t,
    typeName: F.ZodEnum,
    ...le(e)
  });
}
class Or extends pe {
  _parse(e) {
    if (typeof e.data != "string") {
      const r = this._getOrReturnCtx(e), n = this._def.values;
      return J(r, {
        expected: _e.joinValues(n),
        received: r.parsedType,
        code: H.invalid_type
      }), ie;
    }
    if (this._cache || (this._cache = new Set(this._def.values)), !this._cache.has(e.data)) {
      const r = this._getOrReturnCtx(e), n = this._def.values;
      return J(r, {
        received: r.data,
        code: H.invalid_enum_value,
        options: n
      }), ie;
    }
    return dt(e.data);
  }
  get options() {
    return this._def.values;
  }
  get enum() {
    const e = {};
    for (const r of this._def.values)
      e[r] = r;
    return e;
  }
  get Values() {
    const e = {};
    for (const r of this._def.values)
      e[r] = r;
    return e;
  }
  get Enum() {
    const e = {};
    for (const r of this._def.values)
      e[r] = r;
    return e;
  }
  extract(e, r = this._def) {
    return Or.create(e, {
      ...this._def,
      ...r
    });
  }
  exclude(e, r = this._def) {
    return Or.create(this.options.filter((n) => !e.includes(n)), {
      ...this._def,
      ...r
    });
  }
}
Or.create = Th;
class Oc extends pe {
  _parse(e) {
    const r = _e.getValidEnumValues(this._def.values), n = this._getOrReturnCtx(e);
    if (n.parsedType !== Y.string && n.parsedType !== Y.number) {
      const s = _e.objectValues(r);
      return J(n, {
        expected: _e.joinValues(s),
        received: n.parsedType,
        code: H.invalid_type
      }), ie;
    }
    if (this._cache || (this._cache = new Set(_e.getValidEnumValues(this._def.values))), !this._cache.has(e.data)) {
      const s = _e.objectValues(r);
      return J(n, {
        received: n.data,
        code: H.invalid_enum_value,
        options: s
      }), ie;
    }
    return dt(e.data);
  }
  get enum() {
    return this._def.values;
  }
}
Oc.create = (t, e) => new Oc({
  values: t,
  typeName: F.ZodNativeEnum,
  ...le(e)
});
class js extends pe {
  unwrap() {
    return this._def.type;
  }
  _parse(e) {
    const { ctx: r } = this._processInputParams(e);
    if (r.parsedType !== Y.promise && r.common.async === !1)
      return J(r, {
        code: H.invalid_type,
        expected: Y.promise,
        received: r.parsedType
      }), ie;
    const n = r.parsedType === Y.promise ? r.data : Promise.resolve(r.data);
    return dt(n.then((s) => this._def.type.parseAsync(s, {
      path: r.path,
      errorMap: r.common.contextualErrorMap
    })));
  }
}
js.create = (t, e) => new js({
  type: t,
  typeName: F.ZodPromise,
  ...le(e)
});
class Ar extends pe {
  innerType() {
    return this._def.schema;
  }
  sourceType() {
    return this._def.schema._def.typeName === F.ZodEffects ? this._def.schema.sourceType() : this._def.schema;
  }
  _parse(e) {
    const { status: r, ctx: n } = this._processInputParams(e), s = this._def.effect || null, i = {
      addIssue: (o) => {
        J(n, o), o.fatal ? r.abort() : r.dirty();
      },
      get path() {
        return n.path;
      }
    };
    if (i.addIssue = i.addIssue.bind(i), s.type === "preprocess") {
      const o = s.transform(n.data, i);
      if (n.common.async)
        return Promise.resolve(o).then(async (a) => {
          if (r.value === "aborted")
            return ie;
          const c = await this._def.schema._parseAsync({
            data: a,
            path: n.path,
            parent: n
          });
          return c.status === "aborted" ? ie : c.status === "dirty" || r.value === "dirty" ? Yr(c.value) : c;
        });
      {
        if (r.value === "aborted")
          return ie;
        const a = this._def.schema._parseSync({
          data: o,
          path: n.path,
          parent: n
        });
        return a.status === "aborted" ? ie : a.status === "dirty" || r.value === "dirty" ? Yr(a.value) : a;
      }
    }
    if (s.type === "refinement") {
      const o = (a) => {
        const c = s.refinement(a, i);
        if (n.common.async)
          return Promise.resolve(c);
        if (c instanceof Promise)
          throw new Error("Async refinement encountered during synchronous parse operation. Use .parseAsync instead.");
        return a;
      };
      if (n.common.async === !1) {
        const a = this._def.schema._parseSync({
          data: n.data,
          path: n.path,
          parent: n
        });
        return a.status === "aborted" ? ie : (a.status === "dirty" && r.dirty(), o(a.value), { status: r.value, value: a.value });
      } else
        return this._def.schema._parseAsync({ data: n.data, path: n.path, parent: n }).then((a) => a.status === "aborted" ? ie : (a.status === "dirty" && r.dirty(), o(a.value).then(() => ({ status: r.value, value: a.value }))));
    }
    if (s.type === "transform")
      if (n.common.async === !1) {
        const o = this._def.schema._parseSync({
          data: n.data,
          path: n.path,
          parent: n
        });
        if (!Cr(o))
          return ie;
        const a = s.transform(o.value, i);
        if (a instanceof Promise)
          throw new Error("Asynchronous transform encountered during synchronous parse operation. Use .parseAsync instead.");
        return { status: r.value, value: a };
      } else
        return this._def.schema._parseAsync({ data: n.data, path: n.path, parent: n }).then((o) => Cr(o) ? Promise.resolve(s.transform(o.value, i)).then((a) => ({
          status: r.value,
          value: a
        })) : ie);
    _e.assertNever(s);
  }
}
Ar.create = (t, e, r) => new Ar({
  schema: t,
  typeName: F.ZodEffects,
  effect: e,
  ...le(r)
});
Ar.createWithPreprocess = (t, e, r) => new Ar({
  schema: e,
  effect: { type: "preprocess", transform: t },
  typeName: F.ZodEffects,
  ...le(r)
});
class Lt extends pe {
  _parse(e) {
    return this._getType(e) === Y.undefined ? dt(void 0) : this._def.innerType._parse(e);
  }
  unwrap() {
    return this._def.innerType;
  }
}
Lt.create = (t, e) => new Lt({
  innerType: t,
  typeName: F.ZodOptional,
  ...le(e)
});
class Nr extends pe {
  _parse(e) {
    return this._getType(e) === Y.null ? dt(null) : this._def.innerType._parse(e);
  }
  unwrap() {
    return this._def.innerType;
  }
}
Nr.create = (t, e) => new Nr({
  innerType: t,
  typeName: F.ZodNullable,
  ...le(e)
});
class po extends pe {
  _parse(e) {
    const { ctx: r } = this._processInputParams(e);
    let n = r.data;
    return r.parsedType === Y.undefined && (n = this._def.defaultValue()), this._def.innerType._parse({
      data: n,
      path: r.path,
      parent: r
    });
  }
  removeDefault() {
    return this._def.innerType;
  }
}
po.create = (t, e) => new po({
  innerType: t,
  typeName: F.ZodDefault,
  defaultValue: typeof e.default == "function" ? e.default : () => e.default,
  ...le(e)
});
class mo extends pe {
  _parse(e) {
    const { ctx: r } = this._processInputParams(e), n = {
      ...r,
      common: {
        ...r.common,
        issues: []
      }
    }, s = this._def.innerType._parse({
      data: n.data,
      path: n.path,
      parent: {
        ...n
      }
    });
    return As(s) ? s.then((i) => ({
      status: "valid",
      value: i.status === "valid" ? i.value : this._def.catchValue({
        get error() {
          return new At(n.common.issues);
        },
        input: n.data
      })
    })) : {
      status: "valid",
      value: s.status === "valid" ? s.value : this._def.catchValue({
        get error() {
          return new At(n.common.issues);
        },
        input: n.data
      })
    };
  }
  removeCatch() {
    return this._def.innerType;
  }
}
mo.create = (t, e) => new mo({
  innerType: t,
  typeName: F.ZodCatch,
  catchValue: typeof e.catch == "function" ? e.catch : () => e.catch,
  ...le(e)
});
class Ac extends pe {
  _parse(e) {
    if (this._getType(e) !== Y.nan) {
      const n = this._getOrReturnCtx(e);
      return J(n, {
        code: H.invalid_type,
        expected: Y.nan,
        received: n.parsedType
      }), ie;
    }
    return { status: "valid", value: e.data };
  }
}
Ac.create = (t) => new Ac({
  typeName: F.ZodNaN,
  ...le(t)
});
class Fv extends pe {
  _parse(e) {
    const { ctx: r } = this._processInputParams(e), n = r.data;
    return this._def.type._parse({
      data: n,
      path: r.path,
      parent: r
    });
  }
  unwrap() {
    return this._def.type;
  }
}
class ma extends pe {
  _parse(e) {
    const { status: r, ctx: n } = this._processInputParams(e);
    if (n.common.async)
      return (async () => {
        const i = await this._def.in._parseAsync({
          data: n.data,
          path: n.path,
          parent: n
        });
        return i.status === "aborted" ? ie : i.status === "dirty" ? (r.dirty(), Yr(i.value)) : this._def.out._parseAsync({
          data: i.value,
          path: n.path,
          parent: n
        });
      })();
    {
      const s = this._def.in._parseSync({
        data: n.data,
        path: n.path,
        parent: n
      });
      return s.status === "aborted" ? ie : s.status === "dirty" ? (r.dirty(), {
        status: "dirty",
        value: s.value
      }) : this._def.out._parseSync({
        data: s.value,
        path: n.path,
        parent: n
      });
    }
  }
  static create(e, r) {
    return new ma({
      in: e,
      out: r,
      typeName: F.ZodPipeline
    });
  }
}
class go extends pe {
  _parse(e) {
    const r = this._def.innerType._parse(e), n = (s) => (Cr(s) && (s.value = Object.freeze(s.value)), s);
    return As(r) ? r.then((s) => n(s)) : n(r);
  }
  unwrap() {
    return this._def.innerType;
  }
}
go.create = (t, e) => new go({
  innerType: t,
  typeName: F.ZodReadonly,
  ...le(e)
});
var F;
(function(t) {
  t.ZodString = "ZodString", t.ZodNumber = "ZodNumber", t.ZodNaN = "ZodNaN", t.ZodBigInt = "ZodBigInt", t.ZodBoolean = "ZodBoolean", t.ZodDate = "ZodDate", t.ZodSymbol = "ZodSymbol", t.ZodUndefined = "ZodUndefined", t.ZodNull = "ZodNull", t.ZodAny = "ZodAny", t.ZodUnknown = "ZodUnknown", t.ZodNever = "ZodNever", t.ZodVoid = "ZodVoid", t.ZodArray = "ZodArray", t.ZodObject = "ZodObject", t.ZodUnion = "ZodUnion", t.ZodDiscriminatedUnion = "ZodDiscriminatedUnion", t.ZodIntersection = "ZodIntersection", t.ZodTuple = "ZodTuple", t.ZodRecord = "ZodRecord", t.ZodMap = "ZodMap", t.ZodSet = "ZodSet", t.ZodFunction = "ZodFunction", t.ZodLazy = "ZodLazy", t.ZodLiteral = "ZodLiteral", t.ZodEnum = "ZodEnum", t.ZodEffects = "ZodEffects", t.ZodNativeEnum = "ZodNativeEnum", t.ZodOptional = "ZodOptional", t.ZodNullable = "ZodNullable", t.ZodDefault = "ZodDefault", t.ZodCatch = "ZodCatch", t.ZodPromise = "ZodPromise", t.ZodBranded = "ZodBranded", t.ZodPipeline = "ZodPipeline", t.ZodReadonly = "ZodReadonly";
})(F || (F = {}));
Ht.create;
kt.create;
const Vv = qe.create;
xs.create;
zs.create;
sr.create;
Or.create;
js.create;
Lt.create;
Nr.create;
const Bv = /* @__PURE__ */ M("ZodMiniType", (t, e) => {
  if (!t._zod)
    throw new Error("Uninitialized schema in ZodMiniType.");
  Re.init(t, e), t.def = e, t.type = e.type, t.parse = (r, n) => bf(t, r, n, { callee: t.parse }), t.safeParse = (r, n) => zo(t, r, n), t.parseAsync = async (r, n) => Sf(t, r, n, { callee: t.parseAsync }), t.safeParseAsync = async (r, n) => jo(t, r, n), t.check = (...r) => t.clone({
    ...e,
    checks: [
      ...e.checks ?? [],
      ...r.map((n) => typeof n == "function" ? {
        _zod: { check: n, def: { check: "custom" }, onattach: [] }
      } : n)
    ]
  }, { parent: !0 }), t.with = t.check, t.clone = (r, n) => xt(t, r, n), t.brand = () => t, t.register = ((r, n) => (r.add(t, n), t)), t.apply = (r) => r(t);
}), Wv = /* @__PURE__ */ M("ZodMiniObject", (t, e) => {
  xl.init(t, e), Bv.init(t, e), Se(t, "shape", () => e.shape);
});
// @__NO_SIDE_EFFECTS__
function Nc(t, e) {
  const r = {
    type: "object",
    shape: t ?? {},
    ...Q(e)
  };
  return new Wv(r);
}
function Nt(t) {
  return !!t._zod;
}
function vr(t) {
  const e = Object.values(t);
  if (e.length === 0)
    return /* @__PURE__ */ Nc({});
  const r = e.every(Nt), n = e.every((s) => !Nt(s));
  if (r)
    return /* @__PURE__ */ Nc(t);
  if (n)
    return Vv(t);
  throw new Error("Mixed Zod versions detected in object shape.");
}
function _t(t, e) {
  return Nt(t) ? zo(t, e) : t.safeParse(e);
}
async function bi(t, e) {
  return Nt(t) ? await jo(t, e) : await t.safeParseAsync(e);
}
function Mr(t) {
  if (!t)
    return;
  let e;
  if (Nt(t) ? e = t._zod?.def?.shape : e = t.shape, !!e) {
    if (typeof e == "function")
      try {
        return e();
      } catch {
        return;
      }
    return e;
  }
}
function Lr(t) {
  if (t) {
    if (typeof t == "object") {
      const e = t, r = t;
      if (!e._def && !r._zod) {
        const n = Object.values(t);
        if (n.length > 0 && n.every((s) => typeof s == "object" && s !== null && (s._def !== void 0 || s._zod !== void 0 || typeof s.parse == "function")))
          return vr(t);
      }
    }
    if (Nt(t)) {
      const r = t._zod?.def;
      if (r && (r.type === "object" || r.shape !== void 0))
        return t;
    } else if (t.shape !== void 0)
      return t;
  }
}
function Gv(t) {
  return t.length === 0 ? "object root" : t.reduce((e, r, n) => n === 0 ? String(r) : typeof r == "number" ? `${e}[${r}]` : `${e}.${r}`, "");
}
function Si(t) {
  if (t && typeof t == "object") {
    if ("issues" in t && Array.isArray(t.issues) && t.issues.length > 0)
      return t.issues.map((e) => e.path?.length ? `${e.message} at ${Gv(e.path)}` : e.message).join(`
`);
    if ("message" in t && typeof t.message == "string")
      return t.message;
    try {
      return JSON.stringify(t);
    } catch {
      return String(t);
    }
  }
  return String(t);
}
function Jv(t) {
  return t.description;
}
function Kv(t) {
  if (Nt(t))
    return t._zod?.def?.type === "optional";
  const e = t;
  return typeof t.isOptional == "function" ? t.isOptional() : e._def?.typeName === "ZodOptional";
}
function ri(t) {
  if (Nt(t)) {
    const i = t._zod?.def;
    if (i) {
      if (i.value !== void 0)
        return i.value;
      if (Array.isArray(i.values) && i.values.length > 0)
        return i.values[0];
    }
  }
  const r = t._def;
  if (r) {
    if (r.value !== void 0)
      return r.value;
    if (Array.isArray(r.values) && r.values.length > 0)
      return r.values[0];
  }
  const n = t.value;
  if (n !== void 0)
    return n;
}
function Wt(t) {
  return t === "completed" || t === "failed" || t === "cancelled";
}
const Qv = /* @__PURE__ */ Symbol("Let zodToJsonSchema decide on which parser to use"), xc = {
  name: void 0,
  $refStrategy: "root",
  basePath: ["#"],
  effectStrategy: "input",
  pipeStrategy: "all",
  dateStrategy: "format:date-time",
  mapStrategy: "entries",
  removeAdditionalStrategy: "passthrough",
  allowedAdditionalProperties: !0,
  rejectedAdditionalProperties: !1,
  definitionPath: "definitions",
  target: "jsonSchema7",
  strictUnions: !1,
  definitions: {},
  errorMessages: !1,
  markdownDescription: !1,
  patternStrategy: "escape",
  applyRegexFlags: !1,
  emailStrategy: "format:email",
  base64Strategy: "contentEncoding:base64",
  nameStrategy: "ref",
  openAiAnyTypeName: "OpenAiAnyType"
}, Yv = (t) => typeof t == "string" ? {
  ...xc,
  name: t
} : {
  ...xc,
  ...t
}, Xv = (t) => {
  const e = Yv(t), r = e.name !== void 0 ? [...e.basePath, e.definitionPath, e.name] : e.basePath;
  return {
    ...e,
    flags: { hasReferencedOpenAiAnyType: !1 },
    currentPath: r,
    propertyPath: void 0,
    seen: new Map(Object.entries(e.definitions).map(([n, s]) => [
      s._def,
      {
        def: s._def,
        path: [...e.basePath, e.definitionPath, n],
        // Resolution of references will be forced even though seen, so it's ok that the schema is undefined here for now.
        jsonSchema: void 0
      }
    ]))
  };
};
function Rh(t, e, r, n) {
  n?.errorMessages && r && (t.errorMessage = {
    ...t.errorMessage,
    [e]: r
  });
}
function $e(t, e, r, n, s) {
  t[e] = r, Rh(t, e, n, s);
}
const Ih = (t, e) => {
  let r = 0;
  for (; r < t.length && r < e.length && t[r] === e[r]; r++)
    ;
  return [(t.length - r).toString(), ...e.slice(r)].join("/");
};
function et(t) {
  if (t.target !== "openAi")
    return {};
  const e = [
    ...t.basePath,
    t.definitionPath,
    t.openAiAnyTypeName
  ];
  return t.flags.hasReferencedOpenAiAnyType = !0, {
    $ref: t.$refStrategy === "relative" ? Ih(e, t.currentPath) : e.join("/")
  };
}
function eb(t, e) {
  const r = {
    type: "array"
  };
  return t.type?._def && t.type?._def?.typeName !== F.ZodAny && (r.items = we(t.type._def, {
    ...e,
    currentPath: [...e.currentPath, "items"]
  })), t.minLength && $e(r, "minItems", t.minLength.value, t.minLength.message, e), t.maxLength && $e(r, "maxItems", t.maxLength.value, t.maxLength.message, e), t.exactLength && ($e(r, "minItems", t.exactLength.value, t.exactLength.message, e), $e(r, "maxItems", t.exactLength.value, t.exactLength.message, e)), r;
}
function tb(t, e) {
  const r = {
    type: "integer",
    format: "int64"
  };
  if (!t.checks)
    return r;
  for (const n of t.checks)
    switch (n.kind) {
      case "min":
        e.target === "jsonSchema7" ? n.inclusive ? $e(r, "minimum", n.value, n.message, e) : $e(r, "exclusiveMinimum", n.value, n.message, e) : (n.inclusive || (r.exclusiveMinimum = !0), $e(r, "minimum", n.value, n.message, e));
        break;
      case "max":
        e.target === "jsonSchema7" ? n.inclusive ? $e(r, "maximum", n.value, n.message, e) : $e(r, "exclusiveMaximum", n.value, n.message, e) : (n.inclusive || (r.exclusiveMaximum = !0), $e(r, "maximum", n.value, n.message, e));
        break;
      case "multipleOf":
        $e(r, "multipleOf", n.value, n.message, e);
        break;
    }
  return r;
}
function rb() {
  return {
    type: "boolean"
  };
}
function Ph(t, e) {
  return we(t.type._def, e);
}
const nb = (t, e) => we(t.innerType._def, e);
function Ch(t, e, r) {
  const n = r ?? e.dateStrategy;
  if (Array.isArray(n))
    return {
      anyOf: n.map((s, i) => Ch(t, e, s))
    };
  switch (n) {
    case "string":
    case "format:date-time":
      return {
        type: "string",
        format: "date-time"
      };
    case "format:date":
      return {
        type: "string",
        format: "date"
      };
    case "integer":
      return sb(t, e);
  }
}
const sb = (t, e) => {
  const r = {
    type: "integer",
    format: "unix-time"
  };
  if (e.target === "openApi3")
    return r;
  for (const n of t.checks)
    switch (n.kind) {
      case "min":
        $e(
          r,
          "minimum",
          n.value,
          // This is in milliseconds
          n.message,
          e
        );
        break;
      case "max":
        $e(
          r,
          "maximum",
          n.value,
          // This is in milliseconds
          n.message,
          e
        );
        break;
    }
  return r;
};
function ib(t, e) {
  return {
    ...we(t.innerType._def, e),
    default: t.defaultValue()
  };
}
function ob(t, e) {
  return e.effectStrategy === "input" ? we(t.schema._def, e) : et(e);
}
function ab(t) {
  return {
    type: "string",
    enum: Array.from(t.values)
  };
}
const cb = (t) => "type" in t && t.type === "string" ? !1 : "allOf" in t;
function ub(t, e) {
  const r = [
    we(t.left._def, {
      ...e,
      currentPath: [...e.currentPath, "allOf", "0"]
    }),
    we(t.right._def, {
      ...e,
      currentPath: [...e.currentPath, "allOf", "1"]
    })
  ].filter((i) => !!i);
  let n = e.target === "jsonSchema2019-09" ? { unevaluatedProperties: !1 } : void 0;
  const s = [];
  return r.forEach((i) => {
    if (cb(i))
      s.push(...i.allOf), i.unevaluatedProperties === void 0 && (n = void 0);
    else {
      let o = i;
      if ("additionalProperties" in i && i.additionalProperties === !1) {
        const { additionalProperties: a, ...c } = i;
        o = c;
      } else
        n = void 0;
      s.push(o);
    }
  }), s.length ? {
    allOf: s,
    ...n
  } : void 0;
}
function lb(t, e) {
  const r = typeof t.value;
  return r !== "bigint" && r !== "number" && r !== "boolean" && r !== "string" ? {
    type: Array.isArray(t.value) ? "array" : "object"
  } : e.target === "openApi3" ? {
    type: r === "bigint" ? "integer" : r,
    enum: [t.value]
  } : {
    type: r === "bigint" ? "integer" : r,
    const: t.value
  };
}
let ki;
const ft = {
  /**
   * `c` was changed to `[cC]` to replicate /i flag
   */
  cuid: /^[cC][^\s-]{8,}$/,
  cuid2: /^[0-9a-z]+$/,
  ulid: /^[0-9A-HJKMNP-TV-Z]{26}$/,
  /**
   * `a-z` was added to replicate /i flag
   */
  email: /^(?!\.)(?!.*\.\.)([a-zA-Z0-9_'+\-\.]*)[a-zA-Z0-9_+-]@([a-zA-Z0-9][a-zA-Z0-9\-]*\.)+[a-zA-Z]{2,}$/,
  /**
   * Constructed a valid Unicode RegExp
   *
   * Lazily instantiate since this type of regex isn't supported
   * in all envs (e.g. React Native).
   *
   * See:
   * https://github.com/colinhacks/zod/issues/2433
   * Fix in Zod:
   * https://github.com/colinhacks/zod/commit/9340fd51e48576a75adc919bff65dbc4a5d4c99b
   */
  emoji: () => (ki === void 0 && (ki = RegExp("^(\\p{Extended_Pictographic}|\\p{Emoji_Component})+$", "u")), ki),
  /**
   * Unused
   */
  uuid: /^[0-9a-fA-F]{8}\b-[0-9a-fA-F]{4}\b-[0-9a-fA-F]{4}\b-[0-9a-fA-F]{4}\b-[0-9a-fA-F]{12}$/,
  /**
   * Unused
   */
  ipv4: /^(?:(?:25[0-5]|2[0-4][0-9]|1[0-9][0-9]|[1-9][0-9]|[0-9])\.){3}(?:25[0-5]|2[0-4][0-9]|1[0-9][0-9]|[1-9][0-9]|[0-9])$/,
  ipv4Cidr: /^(?:(?:25[0-5]|2[0-4][0-9]|1[0-9][0-9]|[1-9][0-9]|[0-9])\.){3}(?:25[0-5]|2[0-4][0-9]|1[0-9][0-9]|[1-9][0-9]|[0-9])\/(3[0-2]|[12]?[0-9])$/,
  /**
   * Unused
   */
  ipv6: /^(([a-f0-9]{1,4}:){7}|::([a-f0-9]{1,4}:){0,6}|([a-f0-9]{1,4}:){1}:([a-f0-9]{1,4}:){0,5}|([a-f0-9]{1,4}:){2}:([a-f0-9]{1,4}:){0,4}|([a-f0-9]{1,4}:){3}:([a-f0-9]{1,4}:){0,3}|([a-f0-9]{1,4}:){4}:([a-f0-9]{1,4}:){0,2}|([a-f0-9]{1,4}:){5}:([a-f0-9]{1,4}:){0,1})([a-f0-9]{1,4}|(((25[0-5])|(2[0-4][0-9])|(1[0-9]{2})|([0-9]{1,2}))\.){3}((25[0-5])|(2[0-4][0-9])|(1[0-9]{2})|([0-9]{1,2})))$/,
  ipv6Cidr: /^(([0-9a-fA-F]{1,4}:){7,7}[0-9a-fA-F]{1,4}|([0-9a-fA-F]{1,4}:){1,7}:|([0-9a-fA-F]{1,4}:){1,6}:[0-9a-fA-F]{1,4}|([0-9a-fA-F]{1,4}:){1,5}(:[0-9a-fA-F]{1,4}){1,2}|([0-9a-fA-F]{1,4}:){1,4}(:[0-9a-fA-F]{1,4}){1,3}|([0-9a-fA-F]{1,4}:){1,3}(:[0-9a-fA-F]{1,4}){1,4}|([0-9a-fA-F]{1,4}:){1,2}(:[0-9a-fA-F]{1,4}){1,5}|[0-9a-fA-F]{1,4}:((:[0-9a-fA-F]{1,4}){1,6})|:((:[0-9a-fA-F]{1,4}){1,7}|:)|fe80:(:[0-9a-fA-F]{0,4}){0,4}%[0-9a-zA-Z]{1,}|::(ffff(:0{1,4}){0,1}:){0,1}((25[0-5]|(2[0-4]|1{0,1}[0-9]){0,1}[0-9])\.){3,3}(25[0-5]|(2[0-4]|1{0,1}[0-9]){0,1}[0-9])|([0-9a-fA-F]{1,4}:){1,4}:((25[0-5]|(2[0-4]|1{0,1}[0-9]){0,1}[0-9])\.){3,3}(25[0-5]|(2[0-4]|1{0,1}[0-9]){0,1}[0-9]))\/(12[0-8]|1[01][0-9]|[1-9]?[0-9])$/,
  base64: /^([0-9a-zA-Z+/]{4})*(([0-9a-zA-Z+/]{2}==)|([0-9a-zA-Z+/]{3}=))?$/,
  base64url: /^([0-9a-zA-Z-_]{4})*(([0-9a-zA-Z-_]{2}(==)?)|([0-9a-zA-Z-_]{3}(=)?))?$/,
  nanoid: /^[a-zA-Z0-9_-]{21}$/,
  jwt: /^[A-Za-z0-9-_]+\.[A-Za-z0-9-_]+\.[A-Za-z0-9-_]*$/
};
function Oh(t, e) {
  const r = {
    type: "string"
  };
  if (t.checks)
    for (const n of t.checks)
      switch (n.kind) {
        case "min":
          $e(r, "minLength", typeof r.minLength == "number" ? Math.max(r.minLength, n.value) : n.value, n.message, e);
          break;
        case "max":
          $e(r, "maxLength", typeof r.maxLength == "number" ? Math.min(r.maxLength, n.value) : n.value, n.message, e);
          break;
        case "email":
          switch (e.emailStrategy) {
            case "format:email":
              pt(r, "email", n.message, e);
              break;
            case "format:idn-email":
              pt(r, "idn-email", n.message, e);
              break;
            case "pattern:zod":
              Je(r, ft.email, n.message, e);
              break;
          }
          break;
        case "url":
          pt(r, "uri", n.message, e);
          break;
        case "uuid":
          pt(r, "uuid", n.message, e);
          break;
        case "regex":
          Je(r, n.regex, n.message, e);
          break;
        case "cuid":
          Je(r, ft.cuid, n.message, e);
          break;
        case "cuid2":
          Je(r, ft.cuid2, n.message, e);
          break;
        case "startsWith":
          Je(r, RegExp(`^${$i(n.value, e)}`), n.message, e);
          break;
        case "endsWith":
          Je(r, RegExp(`${$i(n.value, e)}$`), n.message, e);
          break;
        case "datetime":
          pt(r, "date-time", n.message, e);
          break;
        case "date":
          pt(r, "date", n.message, e);
          break;
        case "time":
          pt(r, "time", n.message, e);
          break;
        case "duration":
          pt(r, "duration", n.message, e);
          break;
        case "length":
          $e(r, "minLength", typeof r.minLength == "number" ? Math.max(r.minLength, n.value) : n.value, n.message, e), $e(r, "maxLength", typeof r.maxLength == "number" ? Math.min(r.maxLength, n.value) : n.value, n.message, e);
          break;
        case "includes": {
          Je(r, RegExp($i(n.value, e)), n.message, e);
          break;
        }
        case "ip": {
          n.version !== "v6" && pt(r, "ipv4", n.message, e), n.version !== "v4" && pt(r, "ipv6", n.message, e);
          break;
        }
        case "base64url":
          Je(r, ft.base64url, n.message, e);
          break;
        case "jwt":
          Je(r, ft.jwt, n.message, e);
          break;
        case "cidr": {
          n.version !== "v6" && Je(r, ft.ipv4Cidr, n.message, e), n.version !== "v4" && Je(r, ft.ipv6Cidr, n.message, e);
          break;
        }
        case "emoji":
          Je(r, ft.emoji(), n.message, e);
          break;
        case "ulid": {
          Je(r, ft.ulid, n.message, e);
          break;
        }
        case "base64": {
          switch (e.base64Strategy) {
            case "format:binary": {
              pt(r, "binary", n.message, e);
              break;
            }
            case "contentEncoding:base64": {
              $e(r, "contentEncoding", "base64", n.message, e);
              break;
            }
            case "pattern:zod": {
              Je(r, ft.base64, n.message, e);
              break;
            }
          }
          break;
        }
        case "nanoid":
          Je(r, ft.nanoid, n.message, e);
      }
  return r;
}
function $i(t, e) {
  return e.patternStrategy === "escape" ? hb(t) : t;
}
const db = new Set("ABCDEFGHIJKLMNOPQRSTUVXYZabcdefghijklmnopqrstuvxyz0123456789");
function hb(t) {
  let e = "";
  for (let r = 0; r < t.length; r++)
    db.has(t[r]) || (e += "\\"), e += t[r];
  return e;
}
function pt(t, e, r, n) {
  t.format || t.anyOf?.some((s) => s.format) ? (t.anyOf || (t.anyOf = []), t.format && (t.anyOf.push({
    format: t.format,
    ...t.errorMessage && n.errorMessages && {
      errorMessage: { format: t.errorMessage.format }
    }
  }), delete t.format, t.errorMessage && (delete t.errorMessage.format, Object.keys(t.errorMessage).length === 0 && delete t.errorMessage)), t.anyOf.push({
    format: e,
    ...r && n.errorMessages && { errorMessage: { format: r } }
  })) : $e(t, "format", e, r, n);
}
function Je(t, e, r, n) {
  t.pattern || t.allOf?.some((s) => s.pattern) ? (t.allOf || (t.allOf = []), t.pattern && (t.allOf.push({
    pattern: t.pattern,
    ...t.errorMessage && n.errorMessages && {
      errorMessage: { pattern: t.errorMessage.pattern }
    }
  }), delete t.pattern, t.errorMessage && (delete t.errorMessage.pattern, Object.keys(t.errorMessage).length === 0 && delete t.errorMessage)), t.allOf.push({
    pattern: zc(e, n),
    ...r && n.errorMessages && { errorMessage: { pattern: r } }
  })) : $e(t, "pattern", zc(e, n), r, n);
}
function zc(t, e) {
  if (!e.applyRegexFlags || !t.flags)
    return t.source;
  const r = {
    i: t.flags.includes("i"),
    m: t.flags.includes("m"),
    s: t.flags.includes("s")
    // `.` matches newlines
  }, n = r.i ? t.source.toLowerCase() : t.source;
  let s = "", i = !1, o = !1, a = !1;
  for (let c = 0; c < n.length; c++) {
    if (i) {
      s += n[c], i = !1;
      continue;
    }
    if (r.i) {
      if (o) {
        if (n[c].match(/[a-z]/)) {
          a ? (s += n[c], s += `${n[c - 2]}-${n[c]}`.toUpperCase(), a = !1) : n[c + 1] === "-" && n[c + 2]?.match(/[a-z]/) ? (s += n[c], a = !0) : s += `${n[c]}${n[c].toUpperCase()}`;
          continue;
        }
      } else if (n[c].match(/[a-z]/)) {
        s += `[${n[c]}${n[c].toUpperCase()}]`;
        continue;
      }
    }
    if (r.m) {
      if (n[c] === "^") {
        s += `(^|(?<=[\r
]))`;
        continue;
      } else if (n[c] === "$") {
        s += `($|(?=[\r
]))`;
        continue;
      }
    }
    if (r.s && n[c] === ".") {
      s += o ? `${n[c]}\r
` : `[${n[c]}\r
]`;
      continue;
    }
    s += n[c], n[c] === "\\" ? i = !0 : o && n[c] === "]" ? o = !1 : !o && n[c] === "[" && (o = !0);
  }
  try {
    new RegExp(s);
  } catch {
    return console.warn(`Could not convert regex pattern at ${e.currentPath.join("/")} to a flag-independent form! Falling back to the flag-ignorant source`), t.source;
  }
  return s;
}
function Ah(t, e) {
  if (e.target === "openAi" && console.warn("Warning: OpenAI may not support records in schemas! Try an array of key-value pairs instead."), e.target === "openApi3" && t.keyType?._def.typeName === F.ZodEnum)
    return {
      type: "object",
      required: t.keyType._def.values,
      properties: t.keyType._def.values.reduce((n, s) => ({
        ...n,
        [s]: we(t.valueType._def, {
          ...e,
          currentPath: [...e.currentPath, "properties", s]
        }) ?? et(e)
      }), {}),
      additionalProperties: e.rejectedAdditionalProperties
    };
  const r = {
    type: "object",
    additionalProperties: we(t.valueType._def, {
      ...e,
      currentPath: [...e.currentPath, "additionalProperties"]
    }) ?? e.allowedAdditionalProperties
  };
  if (e.target === "openApi3")
    return r;
  if (t.keyType?._def.typeName === F.ZodString && t.keyType._def.checks?.length) {
    const { type: n, ...s } = Oh(t.keyType._def, e);
    return {
      ...r,
      propertyNames: s
    };
  } else {
    if (t.keyType?._def.typeName === F.ZodEnum)
      return {
        ...r,
        propertyNames: {
          enum: t.keyType._def.values
        }
      };
    if (t.keyType?._def.typeName === F.ZodBranded && t.keyType._def.type._def.typeName === F.ZodString && t.keyType._def.type._def.checks?.length) {
      const { type: n, ...s } = Ph(t.keyType._def, e);
      return {
        ...r,
        propertyNames: s
      };
    }
  }
  return r;
}
function fb(t, e) {
  if (e.mapStrategy === "record")
    return Ah(t, e);
  const r = we(t.keyType._def, {
    ...e,
    currentPath: [...e.currentPath, "items", "items", "0"]
  }) || et(e), n = we(t.valueType._def, {
    ...e,
    currentPath: [...e.currentPath, "items", "items", "1"]
  }) || et(e);
  return {
    type: "array",
    maxItems: 125,
    items: {
      type: "array",
      items: [r, n],
      minItems: 2,
      maxItems: 2
    }
  };
}
function pb(t) {
  const e = t.values, n = Object.keys(t.values).filter((i) => typeof e[e[i]] != "number").map((i) => e[i]), s = Array.from(new Set(n.map((i) => typeof i)));
  return {
    type: s.length === 1 ? s[0] === "string" ? "string" : "number" : ["string", "number"],
    enum: n
  };
}
function mb(t) {
  return t.target === "openAi" ? void 0 : {
    not: et({
      ...t,
      currentPath: [...t.currentPath, "not"]
    })
  };
}
function gb(t) {
  return t.target === "openApi3" ? {
    enum: ["null"],
    nullable: !0
  } : {
    type: "null"
  };
}
const Ms = {
  ZodString: "string",
  ZodNumber: "number",
  ZodBigInt: "integer",
  ZodBoolean: "boolean",
  ZodNull: "null"
};
function _b(t, e) {
  if (e.target === "openApi3")
    return jc(t, e);
  const r = t.options instanceof Map ? Array.from(t.options.values()) : t.options;
  if (r.every((n) => n._def.typeName in Ms && (!n._def.checks || !n._def.checks.length))) {
    const n = r.reduce((s, i) => {
      const o = Ms[i._def.typeName];
      return o && !s.includes(o) ? [...s, o] : s;
    }, []);
    return {
      type: n.length > 1 ? n : n[0]
    };
  } else if (r.every((n) => n._def.typeName === "ZodLiteral" && !n.description)) {
    const n = r.reduce((s, i) => {
      const o = typeof i._def.value;
      switch (o) {
        case "string":
        case "number":
        case "boolean":
          return [...s, o];
        case "bigint":
          return [...s, "integer"];
        case "object":
          if (i._def.value === null)
            return [...s, "null"];
        default:
          return s;
      }
    }, []);
    if (n.length === r.length) {
      const s = n.filter((i, o, a) => a.indexOf(i) === o);
      return {
        type: s.length > 1 ? s : s[0],
        enum: r.reduce((i, o) => i.includes(o._def.value) ? i : [...i, o._def.value], [])
      };
    }
  } else if (r.every((n) => n._def.typeName === "ZodEnum"))
    return {
      type: "string",
      enum: r.reduce((n, s) => [
        ...n,
        ...s._def.values.filter((i) => !n.includes(i))
      ], [])
    };
  return jc(t, e);
}
const jc = (t, e) => {
  const r = (t.options instanceof Map ? Array.from(t.options.values()) : t.options).map((n, s) => we(n._def, {
    ...e,
    currentPath: [...e.currentPath, "anyOf", `${s}`]
  })).filter((n) => !!n && (!e.strictUnions || typeof n == "object" && Object.keys(n).length > 0));
  return r.length ? { anyOf: r } : void 0;
};
function yb(t, e) {
  if (["ZodString", "ZodNumber", "ZodBigInt", "ZodBoolean", "ZodNull"].includes(t.innerType._def.typeName) && (!t.innerType._def.checks || !t.innerType._def.checks.length))
    return e.target === "openApi3" ? {
      type: Ms[t.innerType._def.typeName],
      nullable: !0
    } : {
      type: [
        Ms[t.innerType._def.typeName],
        "null"
      ]
    };
  if (e.target === "openApi3") {
    const n = we(t.innerType._def, {
      ...e,
      currentPath: [...e.currentPath]
    });
    return n && "$ref" in n ? { allOf: [n], nullable: !0 } : n && { ...n, nullable: !0 };
  }
  const r = we(t.innerType._def, {
    ...e,
    currentPath: [...e.currentPath, "anyOf", "0"]
  });
  return r && { anyOf: [r, { type: "null" }] };
}
function wb(t, e) {
  const r = {
    type: "number"
  };
  if (!t.checks)
    return r;
  for (const n of t.checks)
    switch (n.kind) {
      case "int":
        r.type = "integer", Rh(r, "type", n.message, e);
        break;
      case "min":
        e.target === "jsonSchema7" ? n.inclusive ? $e(r, "minimum", n.value, n.message, e) : $e(r, "exclusiveMinimum", n.value, n.message, e) : (n.inclusive || (r.exclusiveMinimum = !0), $e(r, "minimum", n.value, n.message, e));
        break;
      case "max":
        e.target === "jsonSchema7" ? n.inclusive ? $e(r, "maximum", n.value, n.message, e) : $e(r, "exclusiveMaximum", n.value, n.message, e) : (n.inclusive || (r.exclusiveMaximum = !0), $e(r, "maximum", n.value, n.message, e));
        break;
      case "multipleOf":
        $e(r, "multipleOf", n.value, n.message, e);
        break;
    }
  return r;
}
function vb(t, e) {
  const r = e.target === "openAi", n = {
    type: "object",
    properties: {}
  }, s = [], i = t.shape();
  for (const a in i) {
    let c = i[a];
    if (c === void 0 || c._def === void 0)
      continue;
    let u = Sb(c);
    u && r && (c._def.typeName === "ZodOptional" && (c = c._def.innerType), c.isNullable() || (c = c.nullable()), u = !1);
    const l = we(c._def, {
      ...e,
      currentPath: [...e.currentPath, "properties", a],
      propertyPath: [...e.currentPath, "properties", a]
    });
    l !== void 0 && (n.properties[a] = l, u || s.push(a));
  }
  s.length && (n.required = s);
  const o = bb(t, e);
  return o !== void 0 && (n.additionalProperties = o), n;
}
function bb(t, e) {
  if (t.catchall._def.typeName !== "ZodNever")
    return we(t.catchall._def, {
      ...e,
      currentPath: [...e.currentPath, "additionalProperties"]
    });
  switch (t.unknownKeys) {
    case "passthrough":
      return e.allowedAdditionalProperties;
    case "strict":
      return e.rejectedAdditionalProperties;
    case "strip":
      return e.removeAdditionalStrategy === "strict" ? e.allowedAdditionalProperties : e.rejectedAdditionalProperties;
  }
}
function Sb(t) {
  try {
    return t.isOptional();
  } catch {
    return !0;
  }
}
const kb = (t, e) => {
  if (e.currentPath.toString() === e.propertyPath?.toString())
    return we(t.innerType._def, e);
  const r = we(t.innerType._def, {
    ...e,
    currentPath: [...e.currentPath, "anyOf", "1"]
  });
  return r ? {
    anyOf: [
      {
        not: et(e)
      },
      r
    ]
  } : et(e);
}, $b = (t, e) => {
  if (e.pipeStrategy === "input")
    return we(t.in._def, e);
  if (e.pipeStrategy === "output")
    return we(t.out._def, e);
  const r = we(t.in._def, {
    ...e,
    currentPath: [...e.currentPath, "allOf", "0"]
  }), n = we(t.out._def, {
    ...e,
    currentPath: [...e.currentPath, "allOf", r ? "1" : "0"]
  });
  return {
    allOf: [r, n].filter((s) => s !== void 0)
  };
};
function Eb(t, e) {
  return we(t.type._def, e);
}
function Tb(t, e) {
  const n = {
    type: "array",
    uniqueItems: !0,
    items: we(t.valueType._def, {
      ...e,
      currentPath: [...e.currentPath, "items"]
    })
  };
  return t.minSize && $e(n, "minItems", t.minSize.value, t.minSize.message, e), t.maxSize && $e(n, "maxItems", t.maxSize.value, t.maxSize.message, e), n;
}
function Rb(t, e) {
  return t.rest ? {
    type: "array",
    minItems: t.items.length,
    items: t.items.map((r, n) => we(r._def, {
      ...e,
      currentPath: [...e.currentPath, "items", `${n}`]
    })).reduce((r, n) => n === void 0 ? r : [...r, n], []),
    additionalItems: we(t.rest._def, {
      ...e,
      currentPath: [...e.currentPath, "additionalItems"]
    })
  } : {
    type: "array",
    minItems: t.items.length,
    maxItems: t.items.length,
    items: t.items.map((r, n) => we(r._def, {
      ...e,
      currentPath: [...e.currentPath, "items", `${n}`]
    })).reduce((r, n) => n === void 0 ? r : [...r, n], [])
  };
}
function Ib(t) {
  return {
    not: et(t)
  };
}
function Pb(t) {
  return et(t);
}
const Cb = (t, e) => we(t.innerType._def, e), Ob = (t, e, r) => {
  switch (e) {
    case F.ZodString:
      return Oh(t, r);
    case F.ZodNumber:
      return wb(t, r);
    case F.ZodObject:
      return vb(t, r);
    case F.ZodBigInt:
      return tb(t, r);
    case F.ZodBoolean:
      return rb();
    case F.ZodDate:
      return Ch(t, r);
    case F.ZodUndefined:
      return Ib(r);
    case F.ZodNull:
      return gb(r);
    case F.ZodArray:
      return eb(t, r);
    case F.ZodUnion:
    case F.ZodDiscriminatedUnion:
      return _b(t, r);
    case F.ZodIntersection:
      return ub(t, r);
    case F.ZodTuple:
      return Rb(t, r);
    case F.ZodRecord:
      return Ah(t, r);
    case F.ZodLiteral:
      return lb(t, r);
    case F.ZodEnum:
      return ab(t);
    case F.ZodNativeEnum:
      return pb(t);
    case F.ZodNullable:
      return yb(t, r);
    case F.ZodOptional:
      return kb(t, r);
    case F.ZodMap:
      return fb(t, r);
    case F.ZodSet:
      return Tb(t, r);
    case F.ZodLazy:
      return () => t.getter()._def;
    case F.ZodPromise:
      return Eb(t, r);
    case F.ZodNaN:
    case F.ZodNever:
      return mb(r);
    case F.ZodEffects:
      return ob(t, r);
    case F.ZodAny:
      return et(r);
    case F.ZodUnknown:
      return Pb(r);
    case F.ZodDefault:
      return ib(t, r);
    case F.ZodBranded:
      return Ph(t, r);
    case F.ZodReadonly:
      return Cb(t, r);
    case F.ZodCatch:
      return nb(t, r);
    case F.ZodPipeline:
      return $b(t, r);
    case F.ZodFunction:
    case F.ZodVoid:
    case F.ZodSymbol:
      return;
    default:
      return /* @__PURE__ */ ((n) => {
      })();
  }
};
function we(t, e, r = !1) {
  const n = e.seen.get(t);
  if (e.override) {
    const a = e.override?.(t, e, n, r);
    if (a !== Qv)
      return a;
  }
  if (n && !r) {
    const a = Ab(n, e);
    if (a !== void 0)
      return a;
  }
  const s = { def: t, path: e.currentPath, jsonSchema: void 0 };
  e.seen.set(t, s);
  const i = Ob(t, t.typeName, e), o = typeof i == "function" ? we(i(), e) : i;
  if (o && Nb(t, e, o), e.postProcess) {
    const a = e.postProcess(o, t, e);
    return s.jsonSchema = o, a;
  }
  return s.jsonSchema = o, o;
}
const Ab = (t, e) => {
  switch (e.$refStrategy) {
    case "root":
      return { $ref: t.path.join("/") };
    case "relative":
      return { $ref: Ih(e.currentPath, t.path) };
    case "none":
    case "seen":
      return t.path.length < e.currentPath.length && t.path.every((r, n) => e.currentPath[n] === r) ? (console.warn(`Recursive reference detected at ${e.currentPath.join("/")}! Defaulting to any`), et(e)) : e.$refStrategy === "seen" ? et(e) : void 0;
  }
}, Nb = (t, e, r) => (t.description && (r.description = t.description, e.markdownDescription && (r.markdownDescription = t.description)), r), xb = (t, e) => {
  const r = Xv(e);
  let n = typeof e == "object" && e.definitions ? Object.entries(e.definitions).reduce((c, [u, l]) => ({
    ...c,
    [u]: we(l._def, {
      ...r,
      currentPath: [...r.basePath, r.definitionPath, u]
    }, !0) ?? et(r)
  }), {}) : void 0;
  const s = typeof e == "string" ? e : e?.nameStrategy === "title" ? void 0 : e?.name, i = we(t._def, s === void 0 ? r : {
    ...r,
    currentPath: [...r.basePath, r.definitionPath, s]
  }, !1) ?? et(r), o = typeof e == "object" && e.name !== void 0 && e.nameStrategy === "title" ? e.name : void 0;
  o !== void 0 && (i.title = o), r.flags.hasReferencedOpenAiAnyType && (n || (n = {}), n[r.openAiAnyTypeName] || (n[r.openAiAnyTypeName] = {
    // Skipping "object" as no properties can be defined and additionalProperties must be "false"
    type: ["string", "number", "integer", "boolean", "array", "null"],
    items: {
      $ref: r.$refStrategy === "relative" ? "1" : [
        ...r.basePath,
        r.definitionPath,
        r.openAiAnyTypeName
      ].join("/")
    }
  }));
  const a = s === void 0 ? n ? {
    ...i,
    [r.definitionPath]: n
  } : i : {
    $ref: [
      ...r.$refStrategy === "relative" ? [] : r.basePath,
      r.definitionPath,
      s
    ].join("/"),
    [r.definitionPath]: {
      ...n,
      [s]: i
    }
  };
  return r.target === "jsonSchema7" ? a.$schema = "http://json-schema.org/draft-07/schema#" : (r.target === "jsonSchema2019-09" || r.target === "openAi") && (a.$schema = "https://json-schema.org/draft/2019-09/schema#"), r.target === "openAi" && ("anyOf" in a || "oneOf" in a || "allOf" in a || "type" in a && Array.isArray(a.type)) && console.warn("Warning: OpenAI may not support schemas with unions as roots! Try wrapping it in an object property."), a;
};
function zb(t) {
  return !t || t === "jsonSchema7" || t === "draft-7" ? "draft-7" : t === "jsonSchema2019-09" || t === "draft-2020-12" ? "draft-2020-12" : "draft-7";
}
function Mc(t, e) {
  return Nt(t) ? Tg(t, {
    target: zb(e?.target),
    io: e?.pipeStrategy ?? "input"
  }) : xb(t, {
    strictUnions: e?.strictUnions ?? !0,
    pipeStrategy: e?.pipeStrategy ?? "input"
  });
}
function qc(t) {
  const r = Mr(t)?.method;
  if (!r)
    throw new Error("Schema is missing a method literal");
  const n = ri(r);
  if (typeof n != "string")
    throw new Error("Schema method literal must be a string");
  return n;
}
function Uc(t, e) {
  const r = _t(t, e);
  if (!r.success)
    throw r.error;
  return r.data;
}
const jb = 6e4;
class Nh {
  constructor(e) {
    this._options = e, this._requestMessageId = 0, this._requestHandlers = /* @__PURE__ */ new Map(), this._requestHandlerAbortControllers = /* @__PURE__ */ new Map(), this._notificationHandlers = /* @__PURE__ */ new Map(), this._responseHandlers = /* @__PURE__ */ new Map(), this._progressHandlers = /* @__PURE__ */ new Map(), this._timeoutInfo = /* @__PURE__ */ new Map(), this._pendingDebouncedNotifications = /* @__PURE__ */ new Set(), this._taskProgressTokens = /* @__PURE__ */ new Map(), this._requestResolvers = /* @__PURE__ */ new Map(), this.setNotificationHandler(Vo, (r) => {
      this._oncancel(r);
    }), this.setNotificationHandler(Go, (r) => {
      this._onprogress(r);
    }), this.setRequestHandler(
      Wo,
      // Automatic pong by default.
      (r) => ({})
    ), this._taskStore = e?.taskStore, this._taskMessageQueue = e?.taskMessageQueue, this._taskStore && (this.setRequestHandler(Jo, async (r, n) => {
      const s = await this._taskStore.getTask(r.params.taskId, n.sessionId);
      if (!s)
        throw new G(K.InvalidParams, "Failed to retrieve task: Task not found");
      return {
        ...s
      };
    }), this.setRequestHandler(Qo, async (r, n) => {
      const s = async () => {
        const i = r.params.taskId;
        if (this._taskMessageQueue) {
          let a;
          for (; a = await this._taskMessageQueue.dequeue(i, n.sessionId); ) {
            if (a.type === "response" || a.type === "error") {
              const c = a.message, u = c.id, l = this._requestResolvers.get(u);
              if (l)
                if (this._requestResolvers.delete(u), a.type === "response")
                  l(c);
                else {
                  const h = c, p = new G(h.error.code, h.error.message, h.error.data);
                  l(p);
                }
              else {
                const h = a.type === "response" ? "Response" : "Error";
                this._onerror(new Error(`${h} handler missing for request ${u}`));
              }
              continue;
            }
            await this._transport?.send(a.message, { relatedRequestId: n.requestId });
          }
        }
        const o = await this._taskStore.getTask(i, n.sessionId);
        if (!o)
          throw new G(K.InvalidParams, `Task not found: ${i}`);
        if (!Wt(o.status))
          return await this._waitForTaskUpdate(i, n.signal), await s();
        if (Wt(o.status)) {
          const a = await this._taskStore.getTaskResult(i, n.sessionId);
          return this._clearTaskQueue(i), {
            ...a,
            _meta: {
              ...a._meta,
              [Yt]: {
                taskId: i
              }
            }
          };
        }
        return await s();
      };
      return await s();
    }), this.setRequestHandler(Yo, async (r, n) => {
      try {
        const { tasks: s, nextCursor: i } = await this._taskStore.listTasks(r.params?.cursor, n.sessionId);
        return {
          tasks: s,
          nextCursor: i,
          _meta: {}
        };
      } catch (s) {
        throw new G(K.InvalidParams, `Failed to list tasks: ${s instanceof Error ? s.message : String(s)}`);
      }
    }), this.setRequestHandler(ea, async (r, n) => {
      try {
        const s = await this._taskStore.getTask(r.params.taskId, n.sessionId);
        if (!s)
          throw new G(K.InvalidParams, `Task not found: ${r.params.taskId}`);
        if (Wt(s.status))
          throw new G(K.InvalidParams, `Cannot cancel task in terminal status: ${s.status}`);
        await this._taskStore.updateTaskStatus(r.params.taskId, "cancelled", "Client cancelled task execution.", n.sessionId), this._clearTaskQueue(r.params.taskId);
        const i = await this._taskStore.getTask(r.params.taskId, n.sessionId);
        if (!i)
          throw new G(K.InvalidParams, `Task not found after cancellation: ${r.params.taskId}`);
        return {
          _meta: {},
          ...i
        };
      } catch (s) {
        throw s instanceof G ? s : new G(K.InvalidRequest, `Failed to cancel task: ${s instanceof Error ? s.message : String(s)}`);
      }
    }));
  }
  async _oncancel(e) {
    if (!e.params.requestId)
      return;
    this._requestHandlerAbortControllers.get(e.params.requestId)?.abort(e.params.reason);
  }
  _setupTimeout(e, r, n, s, i = !1) {
    this._timeoutInfo.set(e, {
      timeoutId: setTimeout(s, r),
      startTime: Date.now(),
      timeout: r,
      maxTotalTimeout: n,
      resetTimeoutOnProgress: i,
      onTimeout: s
    });
  }
  _resetTimeout(e) {
    const r = this._timeoutInfo.get(e);
    if (!r)
      return !1;
    const n = Date.now() - r.startTime;
    if (r.maxTotalTimeout && n >= r.maxTotalTimeout)
      throw this._timeoutInfo.delete(e), G.fromError(K.RequestTimeout, "Maximum total timeout exceeded", {
        maxTotalTimeout: r.maxTotalTimeout,
        totalElapsed: n
      });
    return clearTimeout(r.timeoutId), r.timeoutId = setTimeout(r.onTimeout, r.timeout), !0;
  }
  _cleanupTimeout(e) {
    const r = this._timeoutInfo.get(e);
    r && (clearTimeout(r.timeoutId), this._timeoutInfo.delete(e));
  }
  /**
   * Attaches to the given transport, starts it, and starts listening for messages.
   *
   * The Protocol object assumes ownership of the Transport, replacing any callbacks that have already been set, and expects that it is the only user of the Transport instance going forward.
   */
  async connect(e) {
    if (this._transport)
      throw new Error("Already connected to a transport. Call close() before connecting to a new transport, or use a separate Protocol instance per connection.");
    this._transport = e;
    const r = this.transport?.onclose;
    this._transport.onclose = () => {
      r?.(), this._onclose();
    };
    const n = this.transport?.onerror;
    this._transport.onerror = (i) => {
      n?.(i), this._onerror(i);
    };
    const s = this._transport?.onmessage;
    this._transport.onmessage = (i, o) => {
      s?.(i, o), Wr(i) || W_(i) ? this._onresponse(i) : Bi(i) ? this._onrequest(i, o) : B_(i) ? this._onnotification(i) : this._onerror(new Error(`Unknown message type: ${JSON.stringify(i)}`));
    }, await this._transport.start();
  }
  _onclose() {
    const e = this._responseHandlers;
    this._responseHandlers = /* @__PURE__ */ new Map(), this._progressHandlers.clear(), this._taskProgressTokens.clear(), this._pendingDebouncedNotifications.clear();
    for (const n of this._timeoutInfo.values())
      clearTimeout(n.timeoutId);
    this._timeoutInfo.clear();
    for (const n of this._requestHandlerAbortControllers.values())
      n.abort();
    this._requestHandlerAbortControllers.clear();
    const r = G.fromError(K.ConnectionClosed, "Connection closed");
    this._transport = void 0, this.onclose?.();
    for (const n of e.values())
      n(r);
  }
  _onerror(e) {
    this.onerror?.(e);
  }
  _onnotification(e) {
    const r = this._notificationHandlers.get(e.method) ?? this.fallbackNotificationHandler;
    r !== void 0 && Promise.resolve().then(() => r(e)).catch((n) => this._onerror(new Error(`Uncaught error in notification handler: ${n}`)));
  }
  _onrequest(e, r) {
    const n = this._requestHandlers.get(e.method) ?? this.fallbackRequestHandler, s = this._transport, i = e.params?._meta?.[Yt]?.taskId;
    if (n === void 0) {
      const l = {
        jsonrpc: "2.0",
        id: e.id,
        error: {
          code: K.MethodNotFound,
          message: "Method not found"
        }
      };
      i && this._taskMessageQueue ? this._enqueueTaskMessage(i, {
        type: "error",
        message: l,
        timestamp: Date.now()
      }, s?.sessionId).catch((h) => this._onerror(new Error(`Failed to enqueue error response: ${h}`))) : s?.send(l).catch((h) => this._onerror(new Error(`Failed to send an error response: ${h}`)));
      return;
    }
    const o = new AbortController();
    this._requestHandlerAbortControllers.set(e.id, o);
    const a = V_(e.params) ? e.params.task : void 0, c = this._taskStore ? this.requestTaskStore(e, s?.sessionId) : void 0, u = {
      signal: o.signal,
      sessionId: s?.sessionId,
      _meta: e.params?._meta,
      sendNotification: async (l) => {
        if (o.signal.aborted)
          return;
        const h = { relatedRequestId: e.id };
        i && (h.relatedTask = { taskId: i }), await this.notification(l, h);
      },
      sendRequest: async (l, h, p) => {
        if (o.signal.aborted)
          throw new G(K.ConnectionClosed, "Request was cancelled");
        const g = { ...p, relatedRequestId: e.id };
        i && !g.relatedTask && (g.relatedTask = { taskId: i });
        const S = g.relatedTask?.taskId ?? i;
        return S && c && await c.updateTaskStatus(S, "input_required"), await this.request(l, h, g);
      },
      authInfo: r?.authInfo,
      requestId: e.id,
      requestInfo: r?.requestInfo,
      taskId: i,
      taskStore: c,
      taskRequestedTtl: a?.ttl,
      closeSSEStream: r?.closeSSEStream,
      closeStandaloneSSEStream: r?.closeStandaloneSSEStream
    };
    Promise.resolve().then(() => {
      a && this.assertTaskHandlerCapability(e.method);
    }).then(() => n(e, u)).then(async (l) => {
      if (o.signal.aborted)
        return;
      const h = {
        result: l,
        jsonrpc: "2.0",
        id: e.id
      };
      i && this._taskMessageQueue ? await this._enqueueTaskMessage(i, {
        type: "response",
        message: h,
        timestamp: Date.now()
      }, s?.sessionId) : await s?.send(h);
    }, async (l) => {
      if (o.signal.aborted)
        return;
      const h = {
        jsonrpc: "2.0",
        id: e.id,
        error: {
          code: Number.isSafeInteger(l.code) ? l.code : K.InternalError,
          message: l.message ?? "Internal error",
          ...l.data !== void 0 && { data: l.data }
        }
      };
      i && this._taskMessageQueue ? await this._enqueueTaskMessage(i, {
        type: "error",
        message: h,
        timestamp: Date.now()
      }, s?.sessionId) : await s?.send(h);
    }).catch((l) => this._onerror(new Error(`Failed to send response: ${l}`))).finally(() => {
      this._requestHandlerAbortControllers.get(e.id) === o && this._requestHandlerAbortControllers.delete(e.id);
    });
  }
  _onprogress(e) {
    const { progressToken: r, ...n } = e.params, s = Number(r), i = this._progressHandlers.get(s);
    if (!i) {
      this._onerror(new Error(`Received a progress notification for an unknown token: ${JSON.stringify(e)}`));
      return;
    }
    const o = this._responseHandlers.get(s), a = this._timeoutInfo.get(s);
    if (a && o && a.resetTimeoutOnProgress)
      try {
        this._resetTimeout(s);
      } catch (c) {
        this._responseHandlers.delete(s), this._progressHandlers.delete(s), this._cleanupTimeout(s), o(c);
        return;
      }
    i(n);
  }
  _onresponse(e) {
    const r = Number(e.id), n = this._requestResolvers.get(r);
    if (n) {
      if (this._requestResolvers.delete(r), Wr(e))
        n(e);
      else {
        const o = new G(e.error.code, e.error.message, e.error.data);
        n(o);
      }
      return;
    }
    const s = this._responseHandlers.get(r);
    if (s === void 0) {
      this._onerror(new Error(`Received a response for an unknown message ID: ${JSON.stringify(e)}`));
      return;
    }
    this._responseHandlers.delete(r), this._cleanupTimeout(r);
    let i = !1;
    if (Wr(e) && e.result && typeof e.result == "object") {
      const o = e.result;
      if (o.task && typeof o.task == "object") {
        const a = o.task;
        typeof a.taskId == "string" && (i = !0, this._taskProgressTokens.set(a.taskId, r));
      }
    }
    if (i || this._progressHandlers.delete(r), Wr(e))
      s(e);
    else {
      const o = G.fromError(e.error.code, e.error.message, e.error.data);
      s(o);
    }
  }
  get transport() {
    return this._transport;
  }
  /**
   * Closes the connection.
   */
  async close() {
    await this._transport?.close();
  }
  /**
   * Sends a request and returns an AsyncGenerator that yields response messages.
   * The generator is guaranteed to end with either a 'result' or 'error' message.
   *
   * @example
   * ```typescript
   * const stream = protocol.requestStream(request, resultSchema, options);
   * for await (const message of stream) {
   *   switch (message.type) {
   *     case 'taskCreated':
   *       console.log('Task created:', message.task.taskId);
   *       break;
   *     case 'taskStatus':
   *       console.log('Task status:', message.task.status);
   *       break;
   *     case 'result':
   *       console.log('Final result:', message.result);
   *       break;
   *     case 'error':
   *       console.error('Error:', message.error);
   *       break;
   *   }
   * }
   * ```
   *
   * @experimental Use `client.experimental.tasks.requestStream()` to access this method.
   */
  async *requestStream(e, r, n) {
    const { task: s } = n ?? {};
    if (!s) {
      try {
        yield { type: "result", result: await this.request(e, r, n) };
      } catch (o) {
        yield {
          type: "error",
          error: o instanceof G ? o : new G(K.InternalError, String(o))
        };
      }
      return;
    }
    let i;
    try {
      const o = await this.request(e, Er, n);
      if (o.task)
        i = o.task.taskId, yield { type: "taskCreated", task: o.task };
      else
        throw new G(K.InternalError, "Task creation did not return a task");
      for (; ; ) {
        const a = await this.getTask({ taskId: i }, n);
        if (yield { type: "taskStatus", task: a }, Wt(a.status)) {
          a.status === "completed" ? yield { type: "result", result: await this.getTaskResult({ taskId: i }, r, n) } : a.status === "failed" ? yield {
            type: "error",
            error: new G(K.InternalError, `Task ${i} failed`)
          } : a.status === "cancelled" && (yield {
            type: "error",
            error: new G(K.InternalError, `Task ${i} was cancelled`)
          });
          return;
        }
        if (a.status === "input_required") {
          yield { type: "result", result: await this.getTaskResult({ taskId: i }, r, n) };
          return;
        }
        const c = a.pollInterval ?? this._options?.defaultTaskPollInterval ?? 1e3;
        await new Promise((u) => setTimeout(u, c)), n?.signal?.throwIfAborted();
      }
    } catch (o) {
      yield {
        type: "error",
        error: o instanceof G ? o : new G(K.InternalError, String(o))
      };
    }
  }
  /**
   * Sends a request and waits for a response.
   *
   * Do not use this method to emit notifications! Use notification() instead.
   */
  request(e, r, n) {
    const { relatedRequestId: s, resumptionToken: i, onresumptiontoken: o, task: a, relatedTask: c } = n ?? {};
    return new Promise((u, l) => {
      const h = (m) => {
        l(m);
      };
      if (!this._transport) {
        h(new Error("Not connected"));
        return;
      }
      if (this._options?.enforceStrictCapabilities === !0)
        try {
          this.assertCapabilityForMethod(e.method), a && this.assertTaskCapability(e.method);
        } catch (m) {
          h(m);
          return;
        }
      n?.signal?.throwIfAborted();
      const p = this._requestMessageId++, g = {
        ...e,
        jsonrpc: "2.0",
        id: p
      };
      n?.onprogress && (this._progressHandlers.set(p, n.onprogress), g.params = {
        ...e.params,
        _meta: {
          ...e.params?._meta || {},
          progressToken: p
        }
      }), a && (g.params = {
        ...g.params,
        task: a
      }), c && (g.params = {
        ...g.params,
        _meta: {
          ...g.params?._meta || {},
          [Yt]: c
        }
      });
      const S = (m) => {
        this._responseHandlers.delete(p), this._progressHandlers.delete(p), this._cleanupTimeout(p), this._transport?.send({
          jsonrpc: "2.0",
          method: "notifications/cancelled",
          params: {
            requestId: p,
            reason: String(m)
          }
        }, { relatedRequestId: s, resumptionToken: i, onresumptiontoken: o }).catch(($) => this._onerror(new Error(`Failed to send cancellation: ${$}`)));
        const w = m instanceof G ? m : new G(K.RequestTimeout, String(m));
        l(w);
      };
      this._responseHandlers.set(p, (m) => {
        if (!n?.signal?.aborted) {
          if (m instanceof Error)
            return l(m);
          try {
            const w = _t(r, m.result);
            w.success ? u(w.data) : l(w.error);
          } catch (w) {
            l(w);
          }
        }
      }), n?.signal?.addEventListener("abort", () => {
        S(n?.signal?.reason);
      });
      const k = n?.timeout ?? jb, y = () => S(G.fromError(K.RequestTimeout, "Request timed out", { timeout: k }));
      this._setupTimeout(p, k, n?.maxTotalTimeout, y, n?.resetTimeoutOnProgress ?? !1);
      const b = c?.taskId;
      if (b) {
        const m = (w) => {
          const $ = this._responseHandlers.get(p);
          $ ? $(w) : this._onerror(new Error(`Response handler missing for side-channeled request ${p}`));
        };
        this._requestResolvers.set(p, m), this._enqueueTaskMessage(b, {
          type: "request",
          message: g,
          timestamp: Date.now()
        }).catch((w) => {
          this._cleanupTimeout(p), l(w);
        });
      } else
        this._transport.send(g, { relatedRequestId: s, resumptionToken: i, onresumptiontoken: o }).catch((m) => {
          this._cleanupTimeout(p), l(m);
        });
    });
  }
  /**
   * Gets the current status of a task.
   *
   * @experimental Use `client.experimental.tasks.getTask()` to access this method.
   */
  async getTask(e, r) {
    return this.request({ method: "tasks/get", params: e }, Ko, r);
  }
  /**
   * Retrieves the result of a completed task.
   *
   * @experimental Use `client.experimental.tasks.getTaskResult()` to access this method.
   */
  async getTaskResult(e, r, n) {
    return this.request({ method: "tasks/result", params: e }, r, n);
  }
  /**
   * Lists tasks, optionally starting from a pagination cursor.
   *
   * @experimental Use `client.experimental.tasks.listTasks()` to access this method.
   */
  async listTasks(e, r) {
    return this.request({ method: "tasks/list", params: e }, Xo, r);
  }
  /**
   * Cancels a specific task.
   *
   * @experimental Use `client.experimental.tasks.cancelTask()` to access this method.
   */
  async cancelTask(e, r) {
    return this.request({ method: "tasks/cancel", params: e }, uy, r);
  }
  /**
   * Emits a notification, which is a one-way message that does not expect a response.
   */
  async notification(e, r) {
    if (!this._transport)
      throw new Error("Not connected");
    this.assertNotificationCapability(e.method);
    const n = r?.relatedTask?.taskId;
    if (n) {
      const a = {
        ...e,
        jsonrpc: "2.0",
        params: {
          ...e.params,
          _meta: {
            ...e.params?._meta || {},
            [Yt]: r.relatedTask
          }
        }
      };
      await this._enqueueTaskMessage(n, {
        type: "notification",
        message: a,
        timestamp: Date.now()
      });
      return;
    }
    if ((this._options?.debouncedNotificationMethods ?? []).includes(e.method) && !e.params && !r?.relatedRequestId && !r?.relatedTask) {
      if (this._pendingDebouncedNotifications.has(e.method))
        return;
      this._pendingDebouncedNotifications.add(e.method), Promise.resolve().then(() => {
        if (this._pendingDebouncedNotifications.delete(e.method), !this._transport)
          return;
        let a = {
          ...e,
          jsonrpc: "2.0"
        };
        r?.relatedTask && (a = {
          ...a,
          params: {
            ...a.params,
            _meta: {
              ...a.params?._meta || {},
              [Yt]: r.relatedTask
            }
          }
        }), this._transport?.send(a, r).catch((c) => this._onerror(c));
      });
      return;
    }
    let o = {
      ...e,
      jsonrpc: "2.0"
    };
    r?.relatedTask && (o = {
      ...o,
      params: {
        ...o.params,
        _meta: {
          ...o.params?._meta || {},
          [Yt]: r.relatedTask
        }
      }
    }), await this._transport.send(o, r);
  }
  /**
   * Registers a handler to invoke when this protocol object receives a request with the given method.
   *
   * Note that this will replace any previous request handler for the same method.
   */
  setRequestHandler(e, r) {
    const n = qc(e);
    this.assertRequestHandlerCapability(n), this._requestHandlers.set(n, (s, i) => {
      const o = Uc(e, s);
      return Promise.resolve(r(o, i));
    });
  }
  /**
   * Removes the request handler for the given method.
   */
  removeRequestHandler(e) {
    this._requestHandlers.delete(e);
  }
  /**
   * Asserts that a request handler has not already been set for the given method, in preparation for a new one being automatically installed.
   */
  assertCanSetRequestHandler(e) {
    if (this._requestHandlers.has(e))
      throw new Error(`A request handler for ${e} already exists, which would be overridden`);
  }
  /**
   * Registers a handler to invoke when this protocol object receives a notification with the given method.
   *
   * Note that this will replace any previous notification handler for the same method.
   */
  setNotificationHandler(e, r) {
    const n = qc(e);
    this._notificationHandlers.set(n, (s) => {
      const i = Uc(e, s);
      return Promise.resolve(r(i));
    });
  }
  /**
   * Removes the notification handler for the given method.
   */
  removeNotificationHandler(e) {
    this._notificationHandlers.delete(e);
  }
  /**
   * Cleans up the progress handler associated with a task.
   * This should be called when a task reaches a terminal status.
   */
  _cleanupTaskProgressHandler(e) {
    const r = this._taskProgressTokens.get(e);
    r !== void 0 && (this._progressHandlers.delete(r), this._taskProgressTokens.delete(e));
  }
  /**
   * Enqueues a task-related message for side-channel delivery via tasks/result.
   * @param taskId The task ID to associate the message with
   * @param message The message to enqueue
   * @param sessionId Optional session ID for binding the operation to a specific session
   * @throws Error if taskStore is not configured or if enqueue fails (e.g., queue overflow)
   *
   * Note: If enqueue fails, it's the TaskMessageQueue implementation's responsibility to handle
   * the error appropriately (e.g., by failing the task, logging, etc.). The Protocol layer
   * simply propagates the error.
   */
  async _enqueueTaskMessage(e, r, n) {
    if (!this._taskStore || !this._taskMessageQueue)
      throw new Error("Cannot enqueue task message: taskStore and taskMessageQueue are not configured");
    const s = this._options?.maxTaskQueueSize;
    await this._taskMessageQueue.enqueue(e, r, n, s);
  }
  /**
   * Clears the message queue for a task and rejects any pending request resolvers.
   * @param taskId The task ID whose queue should be cleared
   * @param sessionId Optional session ID for binding the operation to a specific session
   */
  async _clearTaskQueue(e, r) {
    if (this._taskMessageQueue) {
      const n = await this._taskMessageQueue.dequeueAll(e, r);
      for (const s of n)
        if (s.type === "request" && Bi(s.message)) {
          const i = s.message.id, o = this._requestResolvers.get(i);
          o ? (o(new G(K.InternalError, "Task cancelled or completed")), this._requestResolvers.delete(i)) : this._onerror(new Error(`Resolver missing for request ${i} during task ${e} cleanup`));
        }
    }
  }
  /**
   * Waits for a task update (new messages or status change) with abort signal support.
   * Uses polling to check for updates at the task's configured poll interval.
   * @param taskId The task ID to wait for
   * @param signal Abort signal to cancel the wait
   * @returns Promise that resolves when an update occurs or rejects if aborted
   */
  async _waitForTaskUpdate(e, r) {
    let n = this._options?.defaultTaskPollInterval ?? 1e3;
    try {
      const s = await this._taskStore?.getTask(e);
      s?.pollInterval && (n = s.pollInterval);
    } catch {
    }
    return new Promise((s, i) => {
      if (r.aborted) {
        i(new G(K.InvalidRequest, "Request cancelled"));
        return;
      }
      const o = setTimeout(s, n);
      r.addEventListener("abort", () => {
        clearTimeout(o), i(new G(K.InvalidRequest, "Request cancelled"));
      }, { once: !0 });
    });
  }
  requestTaskStore(e, r) {
    const n = this._taskStore;
    if (!n)
      throw new Error("No task store configured");
    return {
      createTask: async (s) => {
        if (!e)
          throw new Error("No request provided");
        return await n.createTask(s, e.id, {
          method: e.method,
          params: e.params
        }, r);
      },
      getTask: async (s) => {
        const i = await n.getTask(s, r);
        if (!i)
          throw new G(K.InvalidParams, "Failed to retrieve task: Task not found");
        return i;
      },
      storeTaskResult: async (s, i, o) => {
        await n.storeTaskResult(s, i, o, r);
        const a = await n.getTask(s, r);
        if (a) {
          const c = Es.parse({
            method: "notifications/tasks/status",
            params: a
          });
          await this.notification(c), Wt(a.status) && this._cleanupTaskProgressHandler(s);
        }
      },
      getTaskResult: (s) => n.getTaskResult(s, r),
      updateTaskStatus: async (s, i, o) => {
        const a = await n.getTask(s, r);
        if (!a)
          throw new G(K.InvalidParams, `Task "${s}" not found - it may have been cleaned up`);
        if (Wt(a.status))
          throw new G(K.InvalidParams, `Cannot update task "${s}" from terminal status "${a.status}" to "${i}". Terminal states (completed, failed, cancelled) cannot transition to other states.`);
        await n.updateTaskStatus(s, i, o, r);
        const c = await n.getTask(s, r);
        if (c) {
          const u = Es.parse({
            method: "notifications/tasks/status",
            params: c
          });
          await this.notification(u), Wt(c.status) && this._cleanupTaskProgressHandler(s);
        }
      },
      listTasks: (s) => n.listTasks(s, r)
    };
  }
}
function Dc(t) {
  return t !== null && typeof t == "object" && !Array.isArray(t);
}
function xh(t, e) {
  const r = { ...t };
  for (const n in e) {
    const s = n, i = e[s];
    if (i === void 0)
      continue;
    const o = r[s];
    Dc(o) && Dc(i) ? r[s] = { ...o, ...i } : r[s] = i;
  }
  return r;
}
function ga(t) {
  return t && t.__esModule && Object.prototype.hasOwnProperty.call(t, "default") ? t.default : t;
}
var In = { exports: {} }, Ei = {}, Tt = {}, Gt = {}, Ti = {}, Ri = {}, Ii = {}, Lc;
function qs() {
  return Lc || (Lc = 1, (function(t) {
    Object.defineProperty(t, "__esModule", { value: !0 }), t.regexpCode = t.getEsmExportName = t.getProperty = t.safeStringify = t.stringify = t.strConcat = t.addCodeArg = t.str = t._ = t.nil = t._Code = t.Name = t.IDENTIFIER = t._CodeOrName = void 0;
    class e {
    }
    t._CodeOrName = e, t.IDENTIFIER = /^[a-z$_][a-z$_0-9]*$/i;
    class r extends e {
      constructor(m) {
        if (super(), !t.IDENTIFIER.test(m))
          throw new Error("CodeGen: name must be a valid identifier");
        this.str = m;
      }
      toString() {
        return this.str;
      }
      emptyStr() {
        return !1;
      }
      get names() {
        return { [this.str]: 1 };
      }
    }
    t.Name = r;
    class n extends e {
      constructor(m) {
        super(), this._items = typeof m == "string" ? [m] : m;
      }
      toString() {
        return this.str;
      }
      emptyStr() {
        if (this._items.length > 1)
          return !1;
        const m = this._items[0];
        return m === "" || m === '""';
      }
      get str() {
        var m;
        return (m = this._str) !== null && m !== void 0 ? m : this._str = this._items.reduce((w, $) => `${w}${$}`, "");
      }
      get names() {
        var m;
        return (m = this._names) !== null && m !== void 0 ? m : this._names = this._items.reduce((w, $) => ($ instanceof r && (w[$.str] = (w[$.str] || 0) + 1), w), {});
      }
    }
    t._Code = n, t.nil = new n("");
    function s(b, ...m) {
      const w = [b[0]];
      let $ = 0;
      for (; $ < m.length; )
        a(w, m[$]), w.push(b[++$]);
      return new n(w);
    }
    t._ = s;
    const i = new n("+");
    function o(b, ...m) {
      const w = [g(b[0])];
      let $ = 0;
      for (; $ < m.length; )
        w.push(i), a(w, m[$]), w.push(i, g(b[++$]));
      return c(w), new n(w);
    }
    t.str = o;
    function a(b, m) {
      m instanceof n ? b.push(...m._items) : m instanceof r ? b.push(m) : b.push(h(m));
    }
    t.addCodeArg = a;
    function c(b) {
      let m = 1;
      for (; m < b.length - 1; ) {
        if (b[m] === i) {
          const w = u(b[m - 1], b[m + 1]);
          if (w !== void 0) {
            b.splice(m - 1, 3, w);
            continue;
          }
          b[m++] = "+";
        }
        m++;
      }
    }
    function u(b, m) {
      if (m === '""')
        return b;
      if (b === '""')
        return m;
      if (typeof b == "string")
        return m instanceof r || b[b.length - 1] !== '"' ? void 0 : typeof m != "string" ? `${b.slice(0, -1)}${m}"` : m[0] === '"' ? b.slice(0, -1) + m.slice(1) : void 0;
      if (typeof m == "string" && m[0] === '"' && !(b instanceof r))
        return `"${b}${m.slice(1)}`;
    }
    function l(b, m) {
      return m.emptyStr() ? b : b.emptyStr() ? m : o`${b}${m}`;
    }
    t.strConcat = l;
    function h(b) {
      return typeof b == "number" || typeof b == "boolean" || b === null ? b : g(Array.isArray(b) ? b.join(",") : b);
    }
    function p(b) {
      return new n(g(b));
    }
    t.stringify = p;
    function g(b) {
      return JSON.stringify(b).replace(/\u2028/g, "\\u2028").replace(/\u2029/g, "\\u2029");
    }
    t.safeStringify = g;
    function S(b) {
      return typeof b == "string" && t.IDENTIFIER.test(b) ? new n(`.${b}`) : s`[${b}]`;
    }
    t.getProperty = S;
    function k(b) {
      if (typeof b == "string" && t.IDENTIFIER.test(b))
        return new n(`${b}`);
      throw new Error(`CodeGen: invalid export name: ${b}, use explicit $id name mapping`);
    }
    t.getEsmExportName = k;
    function y(b) {
      return new n(b.toString());
    }
    t.regexpCode = y;
  })(Ii)), Ii;
}
var Pi = {}, Zc;
function Hc() {
  return Zc || (Zc = 1, (function(t) {
    Object.defineProperty(t, "__esModule", { value: !0 }), t.ValueScope = t.ValueScopeName = t.Scope = t.varKinds = t.UsedValueState = void 0;
    const e = /* @__PURE__ */ qs();
    class r extends Error {
      constructor(u) {
        super(`CodeGen: "code" for ${u} not defined`), this.value = u.value;
      }
    }
    var n;
    (function(c) {
      c[c.Started = 0] = "Started", c[c.Completed = 1] = "Completed";
    })(n || (t.UsedValueState = n = {})), t.varKinds = {
      const: new e.Name("const"),
      let: new e.Name("let"),
      var: new e.Name("var")
    };
    class s {
      constructor({ prefixes: u, parent: l } = {}) {
        this._names = {}, this._prefixes = u, this._parent = l;
      }
      toName(u) {
        return u instanceof e.Name ? u : this.name(u);
      }
      name(u) {
        return new e.Name(this._newName(u));
      }
      _newName(u) {
        const l = this._names[u] || this._nameGroup(u);
        return `${u}${l.index++}`;
      }
      _nameGroup(u) {
        var l, h;
        if (!((h = (l = this._parent) === null || l === void 0 ? void 0 : l._prefixes) === null || h === void 0) && h.has(u) || this._prefixes && !this._prefixes.has(u))
          throw new Error(`CodeGen: prefix "${u}" is not allowed in this scope`);
        return this._names[u] = { prefix: u, index: 0 };
      }
    }
    t.Scope = s;
    class i extends e.Name {
      constructor(u, l) {
        super(l), this.prefix = u;
      }
      setValue(u, { property: l, itemIndex: h }) {
        this.value = u, this.scopePath = (0, e._)`.${new e.Name(l)}[${h}]`;
      }
    }
    t.ValueScopeName = i;
    const o = (0, e._)`\n`;
    class a extends s {
      constructor(u) {
        super(u), this._values = {}, this._scope = u.scope, this.opts = { ...u, _n: u.lines ? o : e.nil };
      }
      get() {
        return this._scope;
      }
      name(u) {
        return new i(u, this._newName(u));
      }
      value(u, l) {
        var h;
        if (l.ref === void 0)
          throw new Error("CodeGen: ref must be passed in value");
        const p = this.toName(u), { prefix: g } = p, S = (h = l.key) !== null && h !== void 0 ? h : l.ref;
        let k = this._values[g];
        if (k) {
          const m = k.get(S);
          if (m)
            return m;
        } else
          k = this._values[g] = /* @__PURE__ */ new Map();
        k.set(S, p);
        const y = this._scope[g] || (this._scope[g] = []), b = y.length;
        return y[b] = l.ref, p.setValue(l, { property: g, itemIndex: b }), p;
      }
      getValue(u, l) {
        const h = this._values[u];
        if (h)
          return h.get(l);
      }
      scopeRefs(u, l = this._values) {
        return this._reduceValues(l, (h) => {
          if (h.scopePath === void 0)
            throw new Error(`CodeGen: name "${h}" has no value`);
          return (0, e._)`${u}${h.scopePath}`;
        });
      }
      scopeCode(u = this._values, l, h) {
        return this._reduceValues(u, (p) => {
          if (p.value === void 0)
            throw new Error(`CodeGen: name "${p}" has no value`);
          return p.value.code;
        }, l, h);
      }
      _reduceValues(u, l, h = {}, p) {
        let g = e.nil;
        for (const S in u) {
          const k = u[S];
          if (!k)
            continue;
          const y = h[S] = h[S] || /* @__PURE__ */ new Map();
          k.forEach((b) => {
            if (y.has(b))
              return;
            y.set(b, n.Started);
            let m = l(b);
            if (m) {
              const w = this.opts.es5 ? t.varKinds.var : t.varKinds.const;
              g = (0, e._)`${g}${w} ${b} = ${m};${this.opts._n}`;
            } else if (m = p?.(b))
              g = (0, e._)`${g}${m}${this.opts._n}`;
            else
              throw new r(b);
            y.set(b, n.Completed);
          });
        }
        return g;
      }
    }
    t.ValueScope = a;
  })(Pi)), Pi;
}
var Fc;
function ce() {
  return Fc || (Fc = 1, (function(t) {
    Object.defineProperty(t, "__esModule", { value: !0 }), t.or = t.and = t.not = t.CodeGen = t.operators = t.varKinds = t.ValueScopeName = t.ValueScope = t.Scope = t.Name = t.regexpCode = t.stringify = t.getProperty = t.nil = t.strConcat = t.str = t._ = void 0;
    const e = /* @__PURE__ */ qs(), r = /* @__PURE__ */ Hc();
    var n = /* @__PURE__ */ qs();
    Object.defineProperty(t, "_", { enumerable: !0, get: function() {
      return n._;
    } }), Object.defineProperty(t, "str", { enumerable: !0, get: function() {
      return n.str;
    } }), Object.defineProperty(t, "strConcat", { enumerable: !0, get: function() {
      return n.strConcat;
    } }), Object.defineProperty(t, "nil", { enumerable: !0, get: function() {
      return n.nil;
    } }), Object.defineProperty(t, "getProperty", { enumerable: !0, get: function() {
      return n.getProperty;
    } }), Object.defineProperty(t, "stringify", { enumerable: !0, get: function() {
      return n.stringify;
    } }), Object.defineProperty(t, "regexpCode", { enumerable: !0, get: function() {
      return n.regexpCode;
    } }), Object.defineProperty(t, "Name", { enumerable: !0, get: function() {
      return n.Name;
    } });
    var s = /* @__PURE__ */ Hc();
    Object.defineProperty(t, "Scope", { enumerable: !0, get: function() {
      return s.Scope;
    } }), Object.defineProperty(t, "ValueScope", { enumerable: !0, get: function() {
      return s.ValueScope;
    } }), Object.defineProperty(t, "ValueScopeName", { enumerable: !0, get: function() {
      return s.ValueScopeName;
    } }), Object.defineProperty(t, "varKinds", { enumerable: !0, get: function() {
      return s.varKinds;
    } }), t.operators = {
      GT: new e._Code(">"),
      GTE: new e._Code(">="),
      LT: new e._Code("<"),
      LTE: new e._Code("<="),
      EQ: new e._Code("==="),
      NEQ: new e._Code("!=="),
      NOT: new e._Code("!"),
      OR: new e._Code("||"),
      AND: new e._Code("&&"),
      ADD: new e._Code("+")
    };
    class i {
      optimizeNodes() {
        return this;
      }
      optimizeNames(v, R) {
        return this;
      }
    }
    class o extends i {
      constructor(v, R, U) {
        super(), this.varKind = v, this.name = R, this.rhs = U;
      }
      render({ es5: v, _n: R }) {
        const U = v ? r.varKinds.var : this.varKind, X = this.rhs === void 0 ? "" : ` = ${this.rhs}`;
        return `${U} ${this.name}${X};` + R;
      }
      optimizeNames(v, R) {
        if (v[this.name.str])
          return this.rhs && (this.rhs = q(this.rhs, v, R)), this;
      }
      get names() {
        return this.rhs instanceof e._CodeOrName ? this.rhs.names : {};
      }
    }
    class a extends i {
      constructor(v, R, U) {
        super(), this.lhs = v, this.rhs = R, this.sideEffects = U;
      }
      render({ _n: v }) {
        return `${this.lhs} = ${this.rhs};` + v;
      }
      optimizeNames(v, R) {
        if (!(this.lhs instanceof e.Name && !v[this.lhs.str] && !this.sideEffects))
          return this.rhs = q(this.rhs, v, R), this;
      }
      get names() {
        const v = this.lhs instanceof e.Name ? {} : { ...this.lhs.names };
        return O(v, this.rhs);
      }
    }
    class c extends a {
      constructor(v, R, U, X) {
        super(v, U, X), this.op = R;
      }
      render({ _n: v }) {
        return `${this.lhs} ${this.op}= ${this.rhs};` + v;
      }
    }
    class u extends i {
      constructor(v) {
        super(), this.label = v, this.names = {};
      }
      render({ _n: v }) {
        return `${this.label}:` + v;
      }
    }
    class l extends i {
      constructor(v) {
        super(), this.label = v, this.names = {};
      }
      render({ _n: v }) {
        return `break${this.label ? ` ${this.label}` : ""};` + v;
      }
    }
    class h extends i {
      constructor(v) {
        super(), this.error = v;
      }
      render({ _n: v }) {
        return `throw ${this.error};` + v;
      }
      get names() {
        return this.error.names;
      }
    }
    class p extends i {
      constructor(v) {
        super(), this.code = v;
      }
      render({ _n: v }) {
        return `${this.code};` + v;
      }
      optimizeNodes() {
        return `${this.code}` ? this : void 0;
      }
      optimizeNames(v, R) {
        return this.code = q(this.code, v, R), this;
      }
      get names() {
        return this.code instanceof e._CodeOrName ? this.code.names : {};
      }
    }
    class g extends i {
      constructor(v = []) {
        super(), this.nodes = v;
      }
      render(v) {
        return this.nodes.reduce((R, U) => R + U.render(v), "");
      }
      optimizeNodes() {
        const { nodes: v } = this;
        let R = v.length;
        for (; R--; ) {
          const U = v[R].optimizeNodes();
          Array.isArray(U) ? v.splice(R, 1, ...U) : U ? v[R] = U : v.splice(R, 1);
        }
        return v.length > 0 ? this : void 0;
      }
      optimizeNames(v, R) {
        const { nodes: U } = this;
        let X = U.length;
        for (; X--; ) {
          const ne = U[X];
          ne.optimizeNames(v, R) || (se(v, ne.names), U.splice(X, 1));
        }
        return U.length > 0 ? this : void 0;
      }
      get names() {
        return this.nodes.reduce((v, R) => j(v, R.names), {});
      }
    }
    class S extends g {
      render(v) {
        return "{" + v._n + super.render(v) + "}" + v._n;
      }
    }
    class k extends g {
    }
    class y extends S {
    }
    y.kind = "else";
    class b extends S {
      constructor(v, R) {
        super(R), this.condition = v;
      }
      render(v) {
        let R = `if(${this.condition})` + super.render(v);
        return this.else && (R += "else " + this.else.render(v)), R;
      }
      optimizeNodes() {
        super.optimizeNodes();
        const v = this.condition;
        if (v === !0)
          return this.nodes;
        let R = this.else;
        if (R) {
          const U = R.optimizeNodes();
          R = this.else = Array.isArray(U) ? new y(U) : U;
        }
        if (R)
          return v === !1 ? R instanceof b ? R : R.nodes : this.nodes.length ? this : new b(Ee(v), R instanceof b ? [R] : R.nodes);
        if (!(v === !1 || !this.nodes.length))
          return this;
      }
      optimizeNames(v, R) {
        var U;
        if (this.else = (U = this.else) === null || U === void 0 ? void 0 : U.optimizeNames(v, R), !!(super.optimizeNames(v, R) || this.else))
          return this.condition = q(this.condition, v, R), this;
      }
      get names() {
        const v = super.names;
        return O(v, this.condition), this.else && j(v, this.else.names), v;
      }
    }
    b.kind = "if";
    class m extends S {
    }
    m.kind = "for";
    class w extends m {
      constructor(v) {
        super(), this.iteration = v;
      }
      render(v) {
        return `for(${this.iteration})` + super.render(v);
      }
      optimizeNames(v, R) {
        if (super.optimizeNames(v, R))
          return this.iteration = q(this.iteration, v, R), this;
      }
      get names() {
        return j(super.names, this.iteration.names);
      }
    }
    class $ extends m {
      constructor(v, R, U, X) {
        super(), this.varKind = v, this.name = R, this.from = U, this.to = X;
      }
      render(v) {
        const R = v.es5 ? r.varKinds.var : this.varKind, { name: U, from: X, to: ne } = this;
        return `for(${R} ${U}=${X}; ${U}<${ne}; ${U}++)` + super.render(v);
      }
      get names() {
        const v = O(super.names, this.from);
        return O(v, this.to);
      }
    }
    class d extends m {
      constructor(v, R, U, X) {
        super(), this.loop = v, this.varKind = R, this.name = U, this.iterable = X;
      }
      render(v) {
        return `for(${this.varKind} ${this.name} ${this.loop} ${this.iterable})` + super.render(v);
      }
      optimizeNames(v, R) {
        if (super.optimizeNames(v, R))
          return this.iterable = q(this.iterable, v, R), this;
      }
      get names() {
        return j(super.names, this.iterable.names);
      }
    }
    class f extends S {
      constructor(v, R, U) {
        super(), this.name = v, this.args = R, this.async = U;
      }
      render(v) {
        return `${this.async ? "async " : ""}function ${this.name}(${this.args})` + super.render(v);
      }
    }
    f.kind = "func";
    class _ extends g {
      render(v) {
        return "return " + super.render(v);
      }
    }
    _.kind = "return";
    class E extends S {
      render(v) {
        let R = "try" + super.render(v);
        return this.catch && (R += this.catch.render(v)), this.finally && (R += this.finally.render(v)), R;
      }
      optimizeNodes() {
        var v, R;
        return super.optimizeNodes(), (v = this.catch) === null || v === void 0 || v.optimizeNodes(), (R = this.finally) === null || R === void 0 || R.optimizeNodes(), this;
      }
      optimizeNames(v, R) {
        var U, X;
        return super.optimizeNames(v, R), (U = this.catch) === null || U === void 0 || U.optimizeNames(v, R), (X = this.finally) === null || X === void 0 || X.optimizeNames(v, R), this;
      }
      get names() {
        const v = super.names;
        return this.catch && j(v, this.catch.names), this.finally && j(v, this.finally.names), v;
      }
    }
    class z extends S {
      constructor(v) {
        super(), this.error = v;
      }
      render(v) {
        return `catch(${this.error})` + super.render(v);
      }
    }
    z.kind = "catch";
    class C extends S {
      render(v) {
        return "finally" + super.render(v);
      }
    }
    C.kind = "finally";
    class P {
      constructor(v, R = {}) {
        this._values = {}, this._blockStarts = [], this._constants = {}, this.opts = { ...R, _n: R.lines ? `
` : "" }, this._extScope = v, this._scope = new r.Scope({ parent: v }), this._nodes = [new k()];
      }
      toString() {
        return this._root.render(this.opts);
      }
      // returns unique name in the internal scope
      name(v) {
        return this._scope.name(v);
      }
      // reserves unique name in the external scope
      scopeName(v) {
        return this._extScope.name(v);
      }
      // reserves unique name in the external scope and assigns value to it
      scopeValue(v, R) {
        const U = this._extScope.value(v, R);
        return (this._values[U.prefix] || (this._values[U.prefix] = /* @__PURE__ */ new Set())).add(U), U;
      }
      getScopeValue(v, R) {
        return this._extScope.getValue(v, R);
      }
      // return code that assigns values in the external scope to the names that are used internally
      // (same names that were returned by gen.scopeName or gen.scopeValue)
      scopeRefs(v) {
        return this._extScope.scopeRefs(v, this._values);
      }
      scopeCode() {
        return this._extScope.scopeCode(this._values);
      }
      _def(v, R, U, X) {
        const ne = this._scope.toName(R);
        return U !== void 0 && X && (this._constants[ne.str] = U), this._leafNode(new o(v, ne, U)), ne;
      }
      // `const` declaration (`var` in es5 mode)
      const(v, R, U) {
        return this._def(r.varKinds.const, v, R, U);
      }
      // `let` declaration with optional assignment (`var` in es5 mode)
      let(v, R, U) {
        return this._def(r.varKinds.let, v, R, U);
      }
      // `var` declaration with optional assignment
      var(v, R, U) {
        return this._def(r.varKinds.var, v, R, U);
      }
      // assignment code
      assign(v, R, U) {
        return this._leafNode(new a(v, R, U));
      }
      // `+=` code
      add(v, R) {
        return this._leafNode(new c(v, t.operators.ADD, R));
      }
      // appends passed SafeExpr to code or executes Block
      code(v) {
        return typeof v == "function" ? v() : v !== e.nil && this._leafNode(new p(v)), this;
      }
      // returns code for object literal for the passed argument list of key-value pairs
      object(...v) {
        const R = ["{"];
        for (const [U, X] of v)
          R.length > 1 && R.push(","), R.push(U), (U !== X || this.opts.es5) && (R.push(":"), (0, e.addCodeArg)(R, X));
        return R.push("}"), new e._Code(R);
      }
      // `if` clause (or statement if `thenBody` and, optionally, `elseBody` are passed)
      if(v, R, U) {
        if (this._blockNode(new b(v)), R && U)
          this.code(R).else().code(U).endIf();
        else if (R)
          this.code(R).endIf();
        else if (U)
          throw new Error('CodeGen: "else" body without "then" body');
        return this;
      }
      // `else if` clause - invalid without `if` or after `else` clauses
      elseIf(v) {
        return this._elseNode(new b(v));
      }
      // `else` clause - only valid after `if` or `else if` clauses
      else() {
        return this._elseNode(new y());
      }
      // end `if` statement (needed if gen.if was used only with condition)
      endIf() {
        return this._endBlockNode(b, y);
      }
      _for(v, R) {
        return this._blockNode(v), R && this.code(R).endFor(), this;
      }
      // a generic `for` clause (or statement if `forBody` is passed)
      for(v, R) {
        return this._for(new w(v), R);
      }
      // `for` statement for a range of values
      forRange(v, R, U, X, ne = this.opts.es5 ? r.varKinds.var : r.varKinds.let) {
        const ye = this._scope.toName(v);
        return this._for(new $(ne, ye, R, U), () => X(ye));
      }
      // `for-of` statement (in es5 mode replace with a normal for loop)
      forOf(v, R, U, X = r.varKinds.const) {
        const ne = this._scope.toName(v);
        if (this.opts.es5) {
          const ye = R instanceof e.Name ? R : this.var("_arr", R);
          return this.forRange("_i", 0, (0, e._)`${ye}.length`, (he) => {
            this.var(ne, (0, e._)`${ye}[${he}]`), U(ne);
          });
        }
        return this._for(new d("of", X, ne, R), () => U(ne));
      }
      // `for-in` statement.
      // With option `ownProperties` replaced with a `for-of` loop for object keys
      forIn(v, R, U, X = this.opts.es5 ? r.varKinds.var : r.varKinds.const) {
        if (this.opts.ownProperties)
          return this.forOf(v, (0, e._)`Object.keys(${R})`, U);
        const ne = this._scope.toName(v);
        return this._for(new d("in", X, ne, R), () => U(ne));
      }
      // end `for` loop
      endFor() {
        return this._endBlockNode(m);
      }
      // `label` statement
      label(v) {
        return this._leafNode(new u(v));
      }
      // `break` statement
      break(v) {
        return this._leafNode(new l(v));
      }
      // `return` statement
      return(v) {
        const R = new _();
        if (this._blockNode(R), this.code(v), R.nodes.length !== 1)
          throw new Error('CodeGen: "return" should have one node');
        return this._endBlockNode(_);
      }
      // `try` statement
      try(v, R, U) {
        if (!R && !U)
          throw new Error('CodeGen: "try" without "catch" and "finally"');
        const X = new E();
        if (this._blockNode(X), this.code(v), R) {
          const ne = this.name("e");
          this._currNode = X.catch = new z(ne), R(ne);
        }
        return U && (this._currNode = X.finally = new C(), this.code(U)), this._endBlockNode(z, C);
      }
      // `throw` statement
      throw(v) {
        return this._leafNode(new h(v));
      }
      // start self-balancing block
      block(v, R) {
        return this._blockStarts.push(this._nodes.length), v && this.code(v).endBlock(R), this;
      }
      // end the current self-balancing block
      endBlock(v) {
        const R = this._blockStarts.pop();
        if (R === void 0)
          throw new Error("CodeGen: not in self-balancing block");
        const U = this._nodes.length - R;
        if (U < 0 || v !== void 0 && U !== v)
          throw new Error(`CodeGen: wrong number of nodes: ${U} vs ${v} expected`);
        return this._nodes.length = R, this;
      }
      // `function` heading (or definition if funcBody is passed)
      func(v, R = e.nil, U, X) {
        return this._blockNode(new f(v, R, U)), X && this.code(X).endFunc(), this;
      }
      // end function definition
      endFunc() {
        return this._endBlockNode(f);
      }
      optimize(v = 1) {
        for (; v-- > 0; )
          this._root.optimizeNodes(), this._root.optimizeNames(this._root.names, this._constants);
      }
      _leafNode(v) {
        return this._currNode.nodes.push(v), this;
      }
      _blockNode(v) {
        this._currNode.nodes.push(v), this._nodes.push(v);
      }
      _endBlockNode(v, R) {
        const U = this._currNode;
        if (U instanceof v || R && U instanceof R)
          return this._nodes.pop(), this;
        throw new Error(`CodeGen: not in block "${R ? `${v.kind}/${R.kind}` : v.kind}"`);
      }
      _elseNode(v) {
        const R = this._currNode;
        if (!(R instanceof b))
          throw new Error('CodeGen: "else" without "if"');
        return this._currNode = R.else = v, this;
      }
      get _root() {
        return this._nodes[0];
      }
      get _currNode() {
        const v = this._nodes;
        return v[v.length - 1];
      }
      set _currNode(v) {
        const R = this._nodes;
        R[R.length - 1] = v;
      }
    }
    t.CodeGen = P;
    function j(x, v) {
      for (const R in v)
        x[R] = (x[R] || 0) + (v[R] || 0);
      return x;
    }
    function O(x, v) {
      return v instanceof e._CodeOrName ? j(x, v.names) : x;
    }
    function q(x, v, R) {
      if (x instanceof e.Name)
        return U(x);
      if (!X(x))
        return x;
      return new e._Code(x._items.reduce((ne, ye) => (ye instanceof e.Name && (ye = U(ye)), ye instanceof e._Code ? ne.push(...ye._items) : ne.push(ye), ne), []));
      function U(ne) {
        const ye = R[ne.str];
        return ye === void 0 || v[ne.str] !== 1 ? ne : (delete v[ne.str], ye);
      }
      function X(ne) {
        return ne instanceof e._Code && ne._items.some((ye) => ye instanceof e.Name && v[ye.str] === 1 && R[ye.str] !== void 0);
      }
    }
    function se(x, v) {
      for (const R in v)
        x[R] = (x[R] || 0) - (v[R] || 0);
    }
    function Ee(x) {
      return typeof x == "boolean" || typeof x == "number" || x === null ? !x : (0, e._)`!${D(x)}`;
    }
    t.not = Ee;
    const be = A(t.operators.AND);
    function oe(...x) {
      return x.reduce(be);
    }
    t.and = oe;
    const Me = A(t.operators.OR);
    function Z(...x) {
      return x.reduce(Me);
    }
    t.or = Z;
    function A(x) {
      return (v, R) => v === e.nil ? R : R === e.nil ? v : (0, e._)`${D(v)} ${x} ${D(R)}`;
    }
    function D(x) {
      return x instanceof e.Name ? x : (0, e._)`(${x})`;
    }
  })(Ri)), Ri;
}
var ae = {}, Vc;
function fe() {
  if (Vc) return ae;
  Vc = 1, Object.defineProperty(ae, "__esModule", { value: !0 }), ae.checkStrictMode = ae.getErrorPath = ae.Type = ae.useFunc = ae.setEvaluated = ae.evaluatedPropsToName = ae.mergeEvaluated = ae.eachItem = ae.unescapeJsonPointer = ae.escapeJsonPointer = ae.escapeFragment = ae.unescapeFragment = ae.schemaRefOrVal = ae.schemaHasRulesButRef = ae.schemaHasRules = ae.checkUnknownRules = ae.alwaysValidSchema = ae.toHash = void 0;
  const t = /* @__PURE__ */ ce(), e = /* @__PURE__ */ qs();
  function r(d) {
    const f = {};
    for (const _ of d)
      f[_] = !0;
    return f;
  }
  ae.toHash = r;
  function n(d, f) {
    return typeof f == "boolean" ? f : Object.keys(f).length === 0 ? !0 : (s(d, f), !i(f, d.self.RULES.all));
  }
  ae.alwaysValidSchema = n;
  function s(d, f = d.schema) {
    const { opts: _, self: E } = d;
    if (!_.strictSchema || typeof f == "boolean")
      return;
    const z = E.RULES.keywords;
    for (const C in f)
      z[C] || $(d, `unknown keyword: "${C}"`);
  }
  ae.checkUnknownRules = s;
  function i(d, f) {
    if (typeof d == "boolean")
      return !d;
    for (const _ in d)
      if (f[_])
        return !0;
    return !1;
  }
  ae.schemaHasRules = i;
  function o(d, f) {
    if (typeof d == "boolean")
      return !d;
    for (const _ in d)
      if (_ !== "$ref" && f.all[_])
        return !0;
    return !1;
  }
  ae.schemaHasRulesButRef = o;
  function a({ topSchemaRef: d, schemaPath: f }, _, E, z) {
    if (!z) {
      if (typeof _ == "number" || typeof _ == "boolean")
        return _;
      if (typeof _ == "string")
        return (0, t._)`${_}`;
    }
    return (0, t._)`${d}${f}${(0, t.getProperty)(E)}`;
  }
  ae.schemaRefOrVal = a;
  function c(d) {
    return h(decodeURIComponent(d));
  }
  ae.unescapeFragment = c;
  function u(d) {
    return encodeURIComponent(l(d));
  }
  ae.escapeFragment = u;
  function l(d) {
    return typeof d == "number" ? `${d}` : d.replace(/~/g, "~0").replace(/\//g, "~1");
  }
  ae.escapeJsonPointer = l;
  function h(d) {
    return d.replace(/~1/g, "/").replace(/~0/g, "~");
  }
  ae.unescapeJsonPointer = h;
  function p(d, f) {
    if (Array.isArray(d))
      for (const _ of d)
        f(_);
    else
      f(d);
  }
  ae.eachItem = p;
  function g({ mergeNames: d, mergeToName: f, mergeValues: _, resultToName: E }) {
    return (z, C, P, j) => {
      const O = P === void 0 ? C : P instanceof t.Name ? (C instanceof t.Name ? d(z, C, P) : f(z, C, P), P) : C instanceof t.Name ? (f(z, P, C), C) : _(C, P);
      return j === t.Name && !(O instanceof t.Name) ? E(z, O) : O;
    };
  }
  ae.mergeEvaluated = {
    props: g({
      mergeNames: (d, f, _) => d.if((0, t._)`${_} !== true && ${f} !== undefined`, () => {
        d.if((0, t._)`${f} === true`, () => d.assign(_, !0), () => d.assign(_, (0, t._)`${_} || {}`).code((0, t._)`Object.assign(${_}, ${f})`));
      }),
      mergeToName: (d, f, _) => d.if((0, t._)`${_} !== true`, () => {
        f === !0 ? d.assign(_, !0) : (d.assign(_, (0, t._)`${_} || {}`), k(d, _, f));
      }),
      mergeValues: (d, f) => d === !0 ? !0 : { ...d, ...f },
      resultToName: S
    }),
    items: g({
      mergeNames: (d, f, _) => d.if((0, t._)`${_} !== true && ${f} !== undefined`, () => d.assign(_, (0, t._)`${f} === true ? true : ${_} > ${f} ? ${_} : ${f}`)),
      mergeToName: (d, f, _) => d.if((0, t._)`${_} !== true`, () => d.assign(_, f === !0 ? !0 : (0, t._)`${_} > ${f} ? ${_} : ${f}`)),
      mergeValues: (d, f) => d === !0 ? !0 : Math.max(d, f),
      resultToName: (d, f) => d.var("items", f)
    })
  };
  function S(d, f) {
    if (f === !0)
      return d.var("props", !0);
    const _ = d.var("props", (0, t._)`{}`);
    return f !== void 0 && k(d, _, f), _;
  }
  ae.evaluatedPropsToName = S;
  function k(d, f, _) {
    Object.keys(_).forEach((E) => d.assign((0, t._)`${f}${(0, t.getProperty)(E)}`, !0));
  }
  ae.setEvaluated = k;
  const y = {};
  function b(d, f) {
    return d.scopeValue("func", {
      ref: f,
      code: y[f.code] || (y[f.code] = new e._Code(f.code))
    });
  }
  ae.useFunc = b;
  var m;
  (function(d) {
    d[d.Num = 0] = "Num", d[d.Str = 1] = "Str";
  })(m || (ae.Type = m = {}));
  function w(d, f, _) {
    if (d instanceof t.Name) {
      const E = f === m.Num;
      return _ ? E ? (0, t._)`"[" + ${d} + "]"` : (0, t._)`"['" + ${d} + "']"` : E ? (0, t._)`"/" + ${d}` : (0, t._)`"/" + ${d}.replace(/~/g, "~0").replace(/\\//g, "~1")`;
    }
    return _ ? (0, t.getProperty)(d).toString() : "/" + l(d);
  }
  ae.getErrorPath = w;
  function $(d, f, _ = d.opts.strictSchema) {
    if (_) {
      if (f = `strict mode: ${f}`, _ === !0)
        throw new Error(f);
      d.self.logger.warn(f);
    }
  }
  return ae.checkStrictMode = $, ae;
}
var Pn = {}, Bc;
function Vt() {
  if (Bc) return Pn;
  Bc = 1, Object.defineProperty(Pn, "__esModule", { value: !0 });
  const t = /* @__PURE__ */ ce(), e = {
    // validation function arguments
    data: new t.Name("data"),
    // data passed to validation function
    // args passed from referencing schema
    valCxt: new t.Name("valCxt"),
    // validation/data context - should not be used directly, it is destructured to the names below
    instancePath: new t.Name("instancePath"),
    parentData: new t.Name("parentData"),
    parentDataProperty: new t.Name("parentDataProperty"),
    rootData: new t.Name("rootData"),
    // root data - same as the data passed to the first/top validation function
    dynamicAnchors: new t.Name("dynamicAnchors"),
    // used to support recursiveRef and dynamicRef
    // function scoped variables
    vErrors: new t.Name("vErrors"),
    // null or array of validation errors
    errors: new t.Name("errors"),
    // counter of validation errors
    this: new t.Name("this"),
    // "globals"
    self: new t.Name("self"),
    scope: new t.Name("scope"),
    // JTD serialize/parse name for JSON string and position
    json: new t.Name("json"),
    jsonPos: new t.Name("jsonPos"),
    jsonLen: new t.Name("jsonLen"),
    jsonPart: new t.Name("jsonPart")
  };
  return Pn.default = e, Pn;
}
var Wc;
function ni() {
  return Wc || (Wc = 1, (function(t) {
    Object.defineProperty(t, "__esModule", { value: !0 }), t.extendErrors = t.resetErrorsCount = t.reportExtraError = t.reportError = t.keyword$DataError = t.keywordError = void 0;
    const e = /* @__PURE__ */ ce(), r = /* @__PURE__ */ fe(), n = /* @__PURE__ */ Vt();
    t.keywordError = {
      message: ({ keyword: y }) => (0, e.str)`must pass "${y}" keyword validation`
    }, t.keyword$DataError = {
      message: ({ keyword: y, schemaType: b }) => b ? (0, e.str)`"${y}" keyword must be ${b} ($data)` : (0, e.str)`"${y}" keyword is invalid ($data)`
    };
    function s(y, b = t.keywordError, m, w) {
      const { it: $ } = y, { gen: d, compositeRule: f, allErrors: _ } = $, E = h(y, b, m);
      w ?? (f || _) ? c(d, E) : u($, (0, e._)`[${E}]`);
    }
    t.reportError = s;
    function i(y, b = t.keywordError, m) {
      const { it: w } = y, { gen: $, compositeRule: d, allErrors: f } = w, _ = h(y, b, m);
      c($, _), d || f || u(w, n.default.vErrors);
    }
    t.reportExtraError = i;
    function o(y, b) {
      y.assign(n.default.errors, b), y.if((0, e._)`${n.default.vErrors} !== null`, () => y.if(b, () => y.assign((0, e._)`${n.default.vErrors}.length`, b), () => y.assign(n.default.vErrors, null)));
    }
    t.resetErrorsCount = o;
    function a({ gen: y, keyword: b, schemaValue: m, data: w, errsCount: $, it: d }) {
      if ($ === void 0)
        throw new Error("ajv implementation error");
      const f = y.name("err");
      y.forRange("i", $, n.default.errors, (_) => {
        y.const(f, (0, e._)`${n.default.vErrors}[${_}]`), y.if((0, e._)`${f}.instancePath === undefined`, () => y.assign((0, e._)`${f}.instancePath`, (0, e.strConcat)(n.default.instancePath, d.errorPath))), y.assign((0, e._)`${f}.schemaPath`, (0, e.str)`${d.errSchemaPath}/${b}`), d.opts.verbose && (y.assign((0, e._)`${f}.schema`, m), y.assign((0, e._)`${f}.data`, w));
      });
    }
    t.extendErrors = a;
    function c(y, b) {
      const m = y.const("err", b);
      y.if((0, e._)`${n.default.vErrors} === null`, () => y.assign(n.default.vErrors, (0, e._)`[${m}]`), (0, e._)`${n.default.vErrors}.push(${m})`), y.code((0, e._)`${n.default.errors}++`);
    }
    function u(y, b) {
      const { gen: m, validateName: w, schemaEnv: $ } = y;
      $.$async ? m.throw((0, e._)`new ${y.ValidationError}(${b})`) : (m.assign((0, e._)`${w}.errors`, b), m.return(!1));
    }
    const l = {
      keyword: new e.Name("keyword"),
      schemaPath: new e.Name("schemaPath"),
      // also used in JTD errors
      params: new e.Name("params"),
      propertyName: new e.Name("propertyName"),
      message: new e.Name("message"),
      schema: new e.Name("schema"),
      parentSchema: new e.Name("parentSchema")
    };
    function h(y, b, m) {
      const { createErrors: w } = y.it;
      return w === !1 ? (0, e._)`{}` : p(y, b, m);
    }
    function p(y, b, m = {}) {
      const { gen: w, it: $ } = y, d = [
        g($, m),
        S(y, m)
      ];
      return k(y, b, d), w.object(...d);
    }
    function g({ errorPath: y }, { instancePath: b }) {
      const m = b ? (0, e.str)`${y}${(0, r.getErrorPath)(b, r.Type.Str)}` : y;
      return [n.default.instancePath, (0, e.strConcat)(n.default.instancePath, m)];
    }
    function S({ keyword: y, it: { errSchemaPath: b } }, { schemaPath: m, parentSchema: w }) {
      let $ = w ? b : (0, e.str)`${b}/${y}`;
      return m && ($ = (0, e.str)`${$}${(0, r.getErrorPath)(m, r.Type.Str)}`), [l.schemaPath, $];
    }
    function k(y, { params: b, message: m }, w) {
      const { keyword: $, data: d, schemaValue: f, it: _ } = y, { opts: E, propertyName: z, topSchemaRef: C, schemaPath: P } = _;
      w.push([l.keyword, $], [l.params, typeof b == "function" ? b(y) : b || (0, e._)`{}`]), E.messages && w.push([l.message, typeof m == "function" ? m(y) : m]), E.verbose && w.push([l.schema, f], [l.parentSchema, (0, e._)`${C}${P}`], [n.default.data, d]), z && w.push([l.propertyName, z]);
    }
  })(Ti)), Ti;
}
var Gc;
function Mb() {
  if (Gc) return Gt;
  Gc = 1, Object.defineProperty(Gt, "__esModule", { value: !0 }), Gt.boolOrEmptySchema = Gt.topBoolOrEmptySchema = void 0;
  const t = /* @__PURE__ */ ni(), e = /* @__PURE__ */ ce(), r = /* @__PURE__ */ Vt(), n = {
    message: "boolean schema is false"
  };
  function s(a) {
    const { gen: c, schema: u, validateName: l } = a;
    u === !1 ? o(a, !1) : typeof u == "object" && u.$async === !0 ? c.return(r.default.data) : (c.assign((0, e._)`${l}.errors`, null), c.return(!0));
  }
  Gt.topBoolOrEmptySchema = s;
  function i(a, c) {
    const { gen: u, schema: l } = a;
    l === !1 ? (u.var(c, !1), o(a)) : u.var(c, !0);
  }
  Gt.boolOrEmptySchema = i;
  function o(a, c) {
    const { gen: u, data: l } = a, h = {
      gen: u,
      keyword: "false schema",
      data: l,
      schema: !1,
      schemaCode: !1,
      schemaValue: !1,
      params: {},
      it: a
    };
    (0, t.reportError)(h, n, void 0, c);
  }
  return Gt;
}
var Fe = {}, Jt = {}, Jc;
function zh() {
  if (Jc) return Jt;
  Jc = 1, Object.defineProperty(Jt, "__esModule", { value: !0 }), Jt.getRules = Jt.isJSONType = void 0;
  const t = ["string", "number", "integer", "boolean", "null", "object", "array"], e = new Set(t);
  function r(s) {
    return typeof s == "string" && e.has(s);
  }
  Jt.isJSONType = r;
  function n() {
    const s = {
      number: { type: "number", rules: [] },
      string: { type: "string", rules: [] },
      array: { type: "array", rules: [] },
      object: { type: "object", rules: [] }
    };
    return {
      types: { ...s, integer: !0, boolean: !0, null: !0 },
      rules: [{ rules: [] }, s.number, s.string, s.array, s.object],
      post: { rules: [] },
      all: {},
      keywords: {}
    };
  }
  return Jt.getRules = n, Jt;
}
var Rt = {}, Kc;
function jh() {
  if (Kc) return Rt;
  Kc = 1, Object.defineProperty(Rt, "__esModule", { value: !0 }), Rt.shouldUseRule = Rt.shouldUseGroup = Rt.schemaHasRulesForType = void 0;
  function t({ schema: n, self: s }, i) {
    const o = s.RULES.types[i];
    return o && o !== !0 && e(n, o);
  }
  Rt.schemaHasRulesForType = t;
  function e(n, s) {
    return s.rules.some((i) => r(n, i));
  }
  Rt.shouldUseGroup = e;
  function r(n, s) {
    var i;
    return n[s.keyword] !== void 0 || ((i = s.definition.implements) === null || i === void 0 ? void 0 : i.some((o) => n[o] !== void 0));
  }
  return Rt.shouldUseRule = r, Rt;
}
var Qc;
function Us() {
  if (Qc) return Fe;
  Qc = 1, Object.defineProperty(Fe, "__esModule", { value: !0 }), Fe.reportTypeError = Fe.checkDataTypes = Fe.checkDataType = Fe.coerceAndCheckDataType = Fe.getJSONTypes = Fe.getSchemaTypes = Fe.DataType = void 0;
  const t = /* @__PURE__ */ zh(), e = /* @__PURE__ */ jh(), r = /* @__PURE__ */ ni(), n = /* @__PURE__ */ ce(), s = /* @__PURE__ */ fe();
  var i;
  (function(m) {
    m[m.Correct = 0] = "Correct", m[m.Wrong = 1] = "Wrong";
  })(i || (Fe.DataType = i = {}));
  function o(m) {
    const w = a(m.type);
    if (w.includes("null")) {
      if (m.nullable === !1)
        throw new Error("type: null contradicts nullable: false");
    } else {
      if (!w.length && m.nullable !== void 0)
        throw new Error('"nullable" cannot be used without "type"');
      m.nullable === !0 && w.push("null");
    }
    return w;
  }
  Fe.getSchemaTypes = o;
  function a(m) {
    const w = Array.isArray(m) ? m : m ? [m] : [];
    if (w.every(t.isJSONType))
      return w;
    throw new Error("type must be JSONType or JSONType[]: " + w.join(","));
  }
  Fe.getJSONTypes = a;
  function c(m, w) {
    const { gen: $, data: d, opts: f } = m, _ = l(w, f.coerceTypes), E = w.length > 0 && !(_.length === 0 && w.length === 1 && (0, e.schemaHasRulesForType)(m, w[0]));
    if (E) {
      const z = S(w, d, f.strictNumbers, i.Wrong);
      $.if(z, () => {
        _.length ? h(m, w, _) : y(m);
      });
    }
    return E;
  }
  Fe.coerceAndCheckDataType = c;
  const u = /* @__PURE__ */ new Set(["string", "number", "integer", "boolean", "null"]);
  function l(m, w) {
    return w ? m.filter(($) => u.has($) || w === "array" && $ === "array") : [];
  }
  function h(m, w, $) {
    const { gen: d, data: f, opts: _ } = m, E = d.let("dataType", (0, n._)`typeof ${f}`), z = d.let("coerced", (0, n._)`undefined`);
    _.coerceTypes === "array" && d.if((0, n._)`${E} == 'object' && Array.isArray(${f}) && ${f}.length == 1`, () => d.assign(f, (0, n._)`${f}[0]`).assign(E, (0, n._)`typeof ${f}`).if(S(w, f, _.strictNumbers), () => d.assign(z, f))), d.if((0, n._)`${z} !== undefined`);
    for (const P of $)
      (u.has(P) || P === "array" && _.coerceTypes === "array") && C(P);
    d.else(), y(m), d.endIf(), d.if((0, n._)`${z} !== undefined`, () => {
      d.assign(f, z), p(m, z);
    });
    function C(P) {
      switch (P) {
        case "string":
          d.elseIf((0, n._)`${E} == "number" || ${E} == "boolean"`).assign(z, (0, n._)`"" + ${f}`).elseIf((0, n._)`${f} === null`).assign(z, (0, n._)`""`);
          return;
        case "number":
          d.elseIf((0, n._)`${E} == "boolean" || ${f} === null
              || (${E} == "string" && ${f} && ${f} == +${f})`).assign(z, (0, n._)`+${f}`);
          return;
        case "integer":
          d.elseIf((0, n._)`${E} === "boolean" || ${f} === null
              || (${E} === "string" && ${f} && ${f} == +${f} && !(${f} % 1))`).assign(z, (0, n._)`+${f}`);
          return;
        case "boolean":
          d.elseIf((0, n._)`${f} === "false" || ${f} === 0 || ${f} === null`).assign(z, !1).elseIf((0, n._)`${f} === "true" || ${f} === 1`).assign(z, !0);
          return;
        case "null":
          d.elseIf((0, n._)`${f} === "" || ${f} === 0 || ${f} === false`), d.assign(z, null);
          return;
        case "array":
          d.elseIf((0, n._)`${E} === "string" || ${E} === "number"
              || ${E} === "boolean" || ${f} === null`).assign(z, (0, n._)`[${f}]`);
      }
    }
  }
  function p({ gen: m, parentData: w, parentDataProperty: $ }, d) {
    m.if((0, n._)`${w} !== undefined`, () => m.assign((0, n._)`${w}[${$}]`, d));
  }
  function g(m, w, $, d = i.Correct) {
    const f = d === i.Correct ? n.operators.EQ : n.operators.NEQ;
    let _;
    switch (m) {
      case "null":
        return (0, n._)`${w} ${f} null`;
      case "array":
        _ = (0, n._)`Array.isArray(${w})`;
        break;
      case "object":
        _ = (0, n._)`${w} && typeof ${w} == "object" && !Array.isArray(${w})`;
        break;
      case "integer":
        _ = E((0, n._)`!(${w} % 1) && !isNaN(${w})`);
        break;
      case "number":
        _ = E();
        break;
      default:
        return (0, n._)`typeof ${w} ${f} ${m}`;
    }
    return d === i.Correct ? _ : (0, n.not)(_);
    function E(z = n.nil) {
      return (0, n.and)((0, n._)`typeof ${w} == "number"`, z, $ ? (0, n._)`isFinite(${w})` : n.nil);
    }
  }
  Fe.checkDataType = g;
  function S(m, w, $, d) {
    if (m.length === 1)
      return g(m[0], w, $, d);
    let f;
    const _ = (0, s.toHash)(m);
    if (_.array && _.object) {
      const E = (0, n._)`typeof ${w} != "object"`;
      f = _.null ? E : (0, n._)`!${w} || ${E}`, delete _.null, delete _.array, delete _.object;
    } else
      f = n.nil;
    _.number && delete _.integer;
    for (const E in _)
      f = (0, n.and)(f, g(E, w, $, d));
    return f;
  }
  Fe.checkDataTypes = S;
  const k = {
    message: ({ schema: m }) => `must be ${m}`,
    params: ({ schema: m, schemaValue: w }) => typeof m == "string" ? (0, n._)`{type: ${m}}` : (0, n._)`{type: ${w}}`
  };
  function y(m) {
    const w = b(m);
    (0, r.reportError)(w, k);
  }
  Fe.reportTypeError = y;
  function b(m) {
    const { gen: w, data: $, schema: d } = m, f = (0, s.schemaRefOrVal)(m, d, "type");
    return {
      gen: w,
      keyword: "type",
      data: $,
      schema: d.type,
      schemaCode: f,
      schemaValue: f,
      parentSchema: d,
      params: {},
      it: m
    };
  }
  return Fe;
}
var Zr = {}, Yc;
function qb() {
  if (Yc) return Zr;
  Yc = 1, Object.defineProperty(Zr, "__esModule", { value: !0 }), Zr.assignDefaults = void 0;
  const t = /* @__PURE__ */ ce(), e = /* @__PURE__ */ fe();
  function r(s, i) {
    const { properties: o, items: a } = s.schema;
    if (i === "object" && o)
      for (const c in o)
        n(s, c, o[c].default);
    else i === "array" && Array.isArray(a) && a.forEach((c, u) => n(s, u, c.default));
  }
  Zr.assignDefaults = r;
  function n(s, i, o) {
    const { gen: a, compositeRule: c, data: u, opts: l } = s;
    if (o === void 0)
      return;
    const h = (0, t._)`${u}${(0, t.getProperty)(i)}`;
    if (c) {
      (0, e.checkStrictMode)(s, `default is ignored for: ${h}`);
      return;
    }
    let p = (0, t._)`${h} === undefined`;
    l.useDefaults === "empty" && (p = (0, t._)`${p} || ${h} === null || ${h} === ""`), a.if(p, (0, t._)`${h} = ${(0, t.stringify)(o)}`);
  }
  return Zr;
}
var mt = {}, ke = {}, Xc;
function yt() {
  if (Xc) return ke;
  Xc = 1, Object.defineProperty(ke, "__esModule", { value: !0 }), ke.validateUnion = ke.validateArray = ke.usePattern = ke.callValidateCode = ke.schemaProperties = ke.allSchemaProperties = ke.noPropertyInData = ke.propertyInData = ke.isOwnProperty = ke.hasPropFunc = ke.reportMissingProp = ke.checkMissingProp = ke.checkReportMissingProp = void 0;
  const t = /* @__PURE__ */ ce(), e = /* @__PURE__ */ fe(), r = /* @__PURE__ */ Vt(), n = /* @__PURE__ */ fe();
  function s(m, w) {
    const { gen: $, data: d, it: f } = m;
    $.if(l($, d, w, f.opts.ownProperties), () => {
      m.setParams({ missingProperty: (0, t._)`${w}` }, !0), m.error();
    });
  }
  ke.checkReportMissingProp = s;
  function i({ gen: m, data: w, it: { opts: $ } }, d, f) {
    return (0, t.or)(...d.map((_) => (0, t.and)(l(m, w, _, $.ownProperties), (0, t._)`${f} = ${_}`)));
  }
  ke.checkMissingProp = i;
  function o(m, w) {
    m.setParams({ missingProperty: w }, !0), m.error();
  }
  ke.reportMissingProp = o;
  function a(m) {
    return m.scopeValue("func", {
      // eslint-disable-next-line @typescript-eslint/unbound-method
      ref: Object.prototype.hasOwnProperty,
      code: (0, t._)`Object.prototype.hasOwnProperty`
    });
  }
  ke.hasPropFunc = a;
  function c(m, w, $) {
    return (0, t._)`${a(m)}.call(${w}, ${$})`;
  }
  ke.isOwnProperty = c;
  function u(m, w, $, d) {
    const f = (0, t._)`${w}${(0, t.getProperty)($)} !== undefined`;
    return d ? (0, t._)`${f} && ${c(m, w, $)}` : f;
  }
  ke.propertyInData = u;
  function l(m, w, $, d) {
    const f = (0, t._)`${w}${(0, t.getProperty)($)} === undefined`;
    return d ? (0, t.or)(f, (0, t.not)(c(m, w, $))) : f;
  }
  ke.noPropertyInData = l;
  function h(m) {
    return m ? Object.keys(m).filter((w) => w !== "__proto__") : [];
  }
  ke.allSchemaProperties = h;
  function p(m, w) {
    return h(w).filter(($) => !(0, e.alwaysValidSchema)(m, w[$]));
  }
  ke.schemaProperties = p;
  function g({ schemaCode: m, data: w, it: { gen: $, topSchemaRef: d, schemaPath: f, errorPath: _ }, it: E }, z, C, P) {
    const j = P ? (0, t._)`${m}, ${w}, ${d}${f}` : w, O = [
      [r.default.instancePath, (0, t.strConcat)(r.default.instancePath, _)],
      [r.default.parentData, E.parentData],
      [r.default.parentDataProperty, E.parentDataProperty],
      [r.default.rootData, r.default.rootData]
    ];
    E.opts.dynamicRef && O.push([r.default.dynamicAnchors, r.default.dynamicAnchors]);
    const q = (0, t._)`${j}, ${$.object(...O)}`;
    return C !== t.nil ? (0, t._)`${z}.call(${C}, ${q})` : (0, t._)`${z}(${q})`;
  }
  ke.callValidateCode = g;
  const S = (0, t._)`new RegExp`;
  function k({ gen: m, it: { opts: w } }, $) {
    const d = w.unicodeRegExp ? "u" : "", { regExp: f } = w.code, _ = f($, d);
    return m.scopeValue("pattern", {
      key: _.toString(),
      ref: _,
      code: (0, t._)`${f.code === "new RegExp" ? S : (0, n.useFunc)(m, f)}(${$}, ${d})`
    });
  }
  ke.usePattern = k;
  function y(m) {
    const { gen: w, data: $, keyword: d, it: f } = m, _ = w.name("valid");
    if (f.allErrors) {
      const z = w.let("valid", !0);
      return E(() => w.assign(z, !1)), z;
    }
    return w.var(_, !0), E(() => w.break()), _;
    function E(z) {
      const C = w.const("len", (0, t._)`${$}.length`);
      w.forRange("i", 0, C, (P) => {
        m.subschema({
          keyword: d,
          dataProp: P,
          dataPropType: e.Type.Num
        }, _), w.if((0, t.not)(_), z);
      });
    }
  }
  ke.validateArray = y;
  function b(m) {
    const { gen: w, schema: $, keyword: d, it: f } = m;
    if (!Array.isArray($))
      throw new Error("ajv implementation error");
    if ($.some((C) => (0, e.alwaysValidSchema)(f, C)) && !f.opts.unevaluated)
      return;
    const E = w.let("valid", !1), z = w.name("_valid");
    w.block(() => $.forEach((C, P) => {
      const j = m.subschema({
        keyword: d,
        schemaProp: P,
        compositeRule: !0
      }, z);
      w.assign(E, (0, t._)`${E} || ${z}`), m.mergeValidEvaluated(j, z) || w.if((0, t.not)(E));
    })), m.result(E, () => m.reset(), () => m.error(!0));
  }
  return ke.validateUnion = b, ke;
}
var eu;
function Ub() {
  if (eu) return mt;
  eu = 1, Object.defineProperty(mt, "__esModule", { value: !0 }), mt.validateKeywordUsage = mt.validSchemaType = mt.funcKeywordCode = mt.macroKeywordCode = void 0;
  const t = /* @__PURE__ */ ce(), e = /* @__PURE__ */ Vt(), r = /* @__PURE__ */ yt(), n = /* @__PURE__ */ ni();
  function s(p, g) {
    const { gen: S, keyword: k, schema: y, parentSchema: b, it: m } = p, w = g.macro.call(m.self, y, b, m), $ = u(S, k, w);
    m.opts.validateSchema !== !1 && m.self.validateSchema(w, !0);
    const d = S.name("valid");
    p.subschema({
      schema: w,
      schemaPath: t.nil,
      errSchemaPath: `${m.errSchemaPath}/${k}`,
      topSchemaRef: $,
      compositeRule: !0
    }, d), p.pass(d, () => p.error(!0));
  }
  mt.macroKeywordCode = s;
  function i(p, g) {
    var S;
    const { gen: k, keyword: y, schema: b, parentSchema: m, $data: w, it: $ } = p;
    c($, g);
    const d = !w && g.compile ? g.compile.call($.self, b, m, $) : g.validate, f = u(k, y, d), _ = k.let("valid");
    p.block$data(_, E), p.ok((S = g.valid) !== null && S !== void 0 ? S : _);
    function E() {
      if (g.errors === !1)
        P(), g.modifying && o(p), j(() => p.error());
      else {
        const O = g.async ? z() : C();
        g.modifying && o(p), j(() => a(p, O));
      }
    }
    function z() {
      const O = k.let("ruleErrs", null);
      return k.try(() => P((0, t._)`await `), (q) => k.assign(_, !1).if((0, t._)`${q} instanceof ${$.ValidationError}`, () => k.assign(O, (0, t._)`${q}.errors`), () => k.throw(q))), O;
    }
    function C() {
      const O = (0, t._)`${f}.errors`;
      return k.assign(O, null), P(t.nil), O;
    }
    function P(O = g.async ? (0, t._)`await ` : t.nil) {
      const q = $.opts.passContext ? e.default.this : e.default.self, se = !("compile" in g && !w || g.schema === !1);
      k.assign(_, (0, t._)`${O}${(0, r.callValidateCode)(p, f, q, se)}`, g.modifying);
    }
    function j(O) {
      var q;
      k.if((0, t.not)((q = g.valid) !== null && q !== void 0 ? q : _), O);
    }
  }
  mt.funcKeywordCode = i;
  function o(p) {
    const { gen: g, data: S, it: k } = p;
    g.if(k.parentData, () => g.assign(S, (0, t._)`${k.parentData}[${k.parentDataProperty}]`));
  }
  function a(p, g) {
    const { gen: S } = p;
    S.if((0, t._)`Array.isArray(${g})`, () => {
      S.assign(e.default.vErrors, (0, t._)`${e.default.vErrors} === null ? ${g} : ${e.default.vErrors}.concat(${g})`).assign(e.default.errors, (0, t._)`${e.default.vErrors}.length`), (0, n.extendErrors)(p);
    }, () => p.error());
  }
  function c({ schemaEnv: p }, g) {
    if (g.async && !p.$async)
      throw new Error("async keyword in sync schema");
  }
  function u(p, g, S) {
    if (S === void 0)
      throw new Error(`keyword "${g}" failed to compile`);
    return p.scopeValue("keyword", typeof S == "function" ? { ref: S } : { ref: S, code: (0, t.stringify)(S) });
  }
  function l(p, g, S = !1) {
    return !g.length || g.some((k) => k === "array" ? Array.isArray(p) : k === "object" ? p && typeof p == "object" && !Array.isArray(p) : typeof p == k || S && typeof p > "u");
  }
  mt.validSchemaType = l;
  function h({ schema: p, opts: g, self: S, errSchemaPath: k }, y, b) {
    if (Array.isArray(y.keyword) ? !y.keyword.includes(b) : y.keyword !== b)
      throw new Error("ajv implementation error");
    const m = y.dependencies;
    if (m?.some((w) => !Object.prototype.hasOwnProperty.call(p, w)))
      throw new Error(`parent schema must have dependencies of ${b}: ${m.join(",")}`);
    if (y.validateSchema && !y.validateSchema(p[b])) {
      const $ = `keyword "${b}" value is invalid at path "${k}": ` + S.errorsText(y.validateSchema.errors);
      if (g.validateSchema === "log")
        S.logger.error($);
      else
        throw new Error($);
    }
  }
  return mt.validateKeywordUsage = h, mt;
}
var It = {}, tu;
function Db() {
  if (tu) return It;
  tu = 1, Object.defineProperty(It, "__esModule", { value: !0 }), It.extendSubschemaMode = It.extendSubschemaData = It.getSubschema = void 0;
  const t = /* @__PURE__ */ ce(), e = /* @__PURE__ */ fe();
  function r(i, { keyword: o, schemaProp: a, schema: c, schemaPath: u, errSchemaPath: l, topSchemaRef: h }) {
    if (o !== void 0 && c !== void 0)
      throw new Error('both "keyword" and "schema" passed, only one allowed');
    if (o !== void 0) {
      const p = i.schema[o];
      return a === void 0 ? {
        schema: p,
        schemaPath: (0, t._)`${i.schemaPath}${(0, t.getProperty)(o)}`,
        errSchemaPath: `${i.errSchemaPath}/${o}`
      } : {
        schema: p[a],
        schemaPath: (0, t._)`${i.schemaPath}${(0, t.getProperty)(o)}${(0, t.getProperty)(a)}`,
        errSchemaPath: `${i.errSchemaPath}/${o}/${(0, e.escapeFragment)(a)}`
      };
    }
    if (c !== void 0) {
      if (u === void 0 || l === void 0 || h === void 0)
        throw new Error('"schemaPath", "errSchemaPath" and "topSchemaRef" are required with "schema"');
      return {
        schema: c,
        schemaPath: u,
        topSchemaRef: h,
        errSchemaPath: l
      };
    }
    throw new Error('either "keyword" or "schema" must be passed');
  }
  It.getSubschema = r;
  function n(i, o, { dataProp: a, dataPropType: c, data: u, dataTypes: l, propertyName: h }) {
    if (u !== void 0 && a !== void 0)
      throw new Error('both "data" and "dataProp" passed, only one allowed');
    const { gen: p } = o;
    if (a !== void 0) {
      const { errorPath: S, dataPathArr: k, opts: y } = o, b = p.let("data", (0, t._)`${o.data}${(0, t.getProperty)(a)}`, !0);
      g(b), i.errorPath = (0, t.str)`${S}${(0, e.getErrorPath)(a, c, y.jsPropertySyntax)}`, i.parentDataProperty = (0, t._)`${a}`, i.dataPathArr = [...k, i.parentDataProperty];
    }
    if (u !== void 0) {
      const S = u instanceof t.Name ? u : p.let("data", u, !0);
      g(S), h !== void 0 && (i.propertyName = h);
    }
    l && (i.dataTypes = l);
    function g(S) {
      i.data = S, i.dataLevel = o.dataLevel + 1, i.dataTypes = [], o.definedProperties = /* @__PURE__ */ new Set(), i.parentData = o.data, i.dataNames = [...o.dataNames, S];
    }
  }
  It.extendSubschemaData = n;
  function s(i, { jtdDiscriminator: o, jtdMetadata: a, compositeRule: c, createErrors: u, allErrors: l }) {
    c !== void 0 && (i.compositeRule = c), u !== void 0 && (i.createErrors = u), l !== void 0 && (i.allErrors = l), i.jtdDiscriminator = o, i.jtdMetadata = a;
  }
  return It.extendSubschemaMode = s, It;
}
var Ke = {}, Ci, ru;
function Mh() {
  return ru || (ru = 1, Ci = function t(e, r) {
    if (e === r) return !0;
    if (e && r && typeof e == "object" && typeof r == "object") {
      if (e.constructor !== r.constructor) return !1;
      var n, s, i;
      if (Array.isArray(e)) {
        if (n = e.length, n != r.length) return !1;
        for (s = n; s-- !== 0; )
          if (!t(e[s], r[s])) return !1;
        return !0;
      }
      if (e.constructor === RegExp) return e.source === r.source && e.flags === r.flags;
      if (e.valueOf !== Object.prototype.valueOf) return e.valueOf() === r.valueOf();
      if (e.toString !== Object.prototype.toString) return e.toString() === r.toString();
      if (i = Object.keys(e), n = i.length, n !== Object.keys(r).length) return !1;
      for (s = n; s-- !== 0; )
        if (!Object.prototype.hasOwnProperty.call(r, i[s])) return !1;
      for (s = n; s-- !== 0; ) {
        var o = i[s];
        if (!t(e[o], r[o])) return !1;
      }
      return !0;
    }
    return e !== e && r !== r;
  }), Ci;
}
var Oi = { exports: {} }, nu;
function Lb() {
  if (nu) return Oi.exports;
  nu = 1;
  var t = Oi.exports = function(n, s, i) {
    typeof s == "function" && (i = s, s = {}), i = s.cb || i;
    var o = typeof i == "function" ? i : i.pre || function() {
    }, a = i.post || function() {
    };
    e(s, o, a, n, "", n);
  };
  t.keywords = {
    additionalItems: !0,
    items: !0,
    contains: !0,
    additionalProperties: !0,
    propertyNames: !0,
    not: !0,
    if: !0,
    then: !0,
    else: !0
  }, t.arrayKeywords = {
    items: !0,
    allOf: !0,
    anyOf: !0,
    oneOf: !0
  }, t.propsKeywords = {
    $defs: !0,
    definitions: !0,
    properties: !0,
    patternProperties: !0,
    dependencies: !0
  }, t.skipKeywords = {
    default: !0,
    enum: !0,
    const: !0,
    required: !0,
    maximum: !0,
    minimum: !0,
    exclusiveMaximum: !0,
    exclusiveMinimum: !0,
    multipleOf: !0,
    maxLength: !0,
    minLength: !0,
    pattern: !0,
    format: !0,
    maxItems: !0,
    minItems: !0,
    uniqueItems: !0,
    maxProperties: !0,
    minProperties: !0
  };
  function e(n, s, i, o, a, c, u, l, h, p) {
    if (o && typeof o == "object" && !Array.isArray(o)) {
      s(o, a, c, u, l, h, p);
      for (var g in o) {
        var S = o[g];
        if (Array.isArray(S)) {
          if (g in t.arrayKeywords)
            for (var k = 0; k < S.length; k++)
              e(n, s, i, S[k], a + "/" + g + "/" + k, c, a, g, o, k);
        } else if (g in t.propsKeywords) {
          if (S && typeof S == "object")
            for (var y in S)
              e(n, s, i, S[y], a + "/" + g + "/" + r(y), c, a, g, o, y);
        } else (g in t.keywords || n.allKeys && !(g in t.skipKeywords)) && e(n, s, i, S, a + "/" + g, c, a, g, o);
      }
      i(o, a, c, u, l, h, p);
    }
  }
  function r(n) {
    return n.replace(/~/g, "~0").replace(/\//g, "~1");
  }
  return Oi.exports;
}
var su;
function si() {
  if (su) return Ke;
  su = 1, Object.defineProperty(Ke, "__esModule", { value: !0 }), Ke.getSchemaRefs = Ke.resolveUrl = Ke.normalizeId = Ke._getFullPath = Ke.getFullPath = Ke.inlineRef = void 0;
  const t = /* @__PURE__ */ fe(), e = Mh(), r = Lb(), n = /* @__PURE__ */ new Set([
    "type",
    "format",
    "pattern",
    "maxLength",
    "minLength",
    "maxProperties",
    "minProperties",
    "maxItems",
    "minItems",
    "maximum",
    "minimum",
    "uniqueItems",
    "multipleOf",
    "required",
    "enum",
    "const"
  ]);
  function s(k, y = !0) {
    return typeof k == "boolean" ? !0 : y === !0 ? !o(k) : y ? a(k) <= y : !1;
  }
  Ke.inlineRef = s;
  const i = /* @__PURE__ */ new Set([
    "$ref",
    "$recursiveRef",
    "$recursiveAnchor",
    "$dynamicRef",
    "$dynamicAnchor"
  ]);
  function o(k) {
    for (const y in k) {
      if (i.has(y))
        return !0;
      const b = k[y];
      if (Array.isArray(b) && b.some(o) || typeof b == "object" && o(b))
        return !0;
    }
    return !1;
  }
  function a(k) {
    let y = 0;
    for (const b in k) {
      if (b === "$ref")
        return 1 / 0;
      if (y++, !n.has(b) && (typeof k[b] == "object" && (0, t.eachItem)(k[b], (m) => y += a(m)), y === 1 / 0))
        return 1 / 0;
    }
    return y;
  }
  function c(k, y = "", b) {
    b !== !1 && (y = h(y));
    const m = k.parse(y);
    return u(k, m);
  }
  Ke.getFullPath = c;
  function u(k, y) {
    return k.serialize(y).split("#")[0] + "#";
  }
  Ke._getFullPath = u;
  const l = /#\/?$/;
  function h(k) {
    return k ? k.replace(l, "") : "";
  }
  Ke.normalizeId = h;
  function p(k, y, b) {
    return b = h(b), k.resolve(y, b);
  }
  Ke.resolveUrl = p;
  const g = /^[a-z_][-a-z0-9._]*$/i;
  function S(k, y) {
    if (typeof k == "boolean")
      return {};
    const { schemaId: b, uriResolver: m } = this.opts, w = h(k[b] || y), $ = { "": w }, d = c(m, w, !1), f = {}, _ = /* @__PURE__ */ new Set();
    return r(k, { allKeys: !0 }, (C, P, j, O) => {
      if (O === void 0)
        return;
      const q = d + P;
      let se = $[O];
      typeof C[b] == "string" && (se = Ee.call(this, C[b])), be.call(this, C.$anchor), be.call(this, C.$dynamicAnchor), $[P] = se;
      function Ee(oe) {
        const Me = this.opts.uriResolver.resolve;
        if (oe = h(se ? Me(se, oe) : oe), _.has(oe))
          throw z(oe);
        _.add(oe);
        let Z = this.refs[oe];
        return typeof Z == "string" && (Z = this.refs[Z]), typeof Z == "object" ? E(C, Z.schema, oe) : oe !== h(q) && (oe[0] === "#" ? (E(C, f[oe], oe), f[oe] = C) : this.refs[oe] = q), oe;
      }
      function be(oe) {
        if (typeof oe == "string") {
          if (!g.test(oe))
            throw new Error(`invalid anchor "${oe}"`);
          Ee.call(this, `#${oe}`);
        }
      }
    }), f;
    function E(C, P, j) {
      if (P !== void 0 && !e(C, P))
        throw z(j);
    }
    function z(C) {
      return new Error(`reference "${C}" resolves to more than one schema`);
    }
  }
  return Ke.getSchemaRefs = S, Ke;
}
var iu;
function ii() {
  if (iu) return Tt;
  iu = 1, Object.defineProperty(Tt, "__esModule", { value: !0 }), Tt.getData = Tt.KeywordCxt = Tt.validateFunctionCode = void 0;
  const t = /* @__PURE__ */ Mb(), e = /* @__PURE__ */ Us(), r = /* @__PURE__ */ jh(), n = /* @__PURE__ */ Us(), s = /* @__PURE__ */ qb(), i = /* @__PURE__ */ Ub(), o = /* @__PURE__ */ Db(), a = /* @__PURE__ */ ce(), c = /* @__PURE__ */ Vt(), u = /* @__PURE__ */ si(), l = /* @__PURE__ */ fe(), h = /* @__PURE__ */ ni();
  function p(I) {
    if (d(I) && (_(I), $(I))) {
      y(I);
      return;
    }
    g(I, () => (0, t.topBoolOrEmptySchema)(I));
  }
  Tt.validateFunctionCode = p;
  function g({ gen: I, validateName: N, schema: L, schemaEnv: V, opts: re }, ue) {
    re.code.es5 ? I.func(N, (0, a._)`${c.default.data}, ${c.default.valCxt}`, V.$async, () => {
      I.code((0, a._)`"use strict"; ${m(L, re)}`), k(I, re), I.code(ue);
    }) : I.func(N, (0, a._)`${c.default.data}, ${S(re)}`, V.$async, () => I.code(m(L, re)).code(ue));
  }
  function S(I) {
    return (0, a._)`{${c.default.instancePath}="", ${c.default.parentData}, ${c.default.parentDataProperty}, ${c.default.rootData}=${c.default.data}${I.dynamicRef ? (0, a._)`, ${c.default.dynamicAnchors}={}` : a.nil}}={}`;
  }
  function k(I, N) {
    I.if(c.default.valCxt, () => {
      I.var(c.default.instancePath, (0, a._)`${c.default.valCxt}.${c.default.instancePath}`), I.var(c.default.parentData, (0, a._)`${c.default.valCxt}.${c.default.parentData}`), I.var(c.default.parentDataProperty, (0, a._)`${c.default.valCxt}.${c.default.parentDataProperty}`), I.var(c.default.rootData, (0, a._)`${c.default.valCxt}.${c.default.rootData}`), N.dynamicRef && I.var(c.default.dynamicAnchors, (0, a._)`${c.default.valCxt}.${c.default.dynamicAnchors}`);
    }, () => {
      I.var(c.default.instancePath, (0, a._)`""`), I.var(c.default.parentData, (0, a._)`undefined`), I.var(c.default.parentDataProperty, (0, a._)`undefined`), I.var(c.default.rootData, c.default.data), N.dynamicRef && I.var(c.default.dynamicAnchors, (0, a._)`{}`);
    });
  }
  function y(I) {
    const { schema: N, opts: L, gen: V } = I;
    g(I, () => {
      L.$comment && N.$comment && O(I), C(I), V.let(c.default.vErrors, null), V.let(c.default.errors, 0), L.unevaluated && b(I), E(I), q(I);
    });
  }
  function b(I) {
    const { gen: N, validateName: L } = I;
    I.evaluated = N.const("evaluated", (0, a._)`${L}.evaluated`), N.if((0, a._)`${I.evaluated}.dynamicProps`, () => N.assign((0, a._)`${I.evaluated}.props`, (0, a._)`undefined`)), N.if((0, a._)`${I.evaluated}.dynamicItems`, () => N.assign((0, a._)`${I.evaluated}.items`, (0, a._)`undefined`));
  }
  function m(I, N) {
    const L = typeof I == "object" && I[N.schemaId];
    return L && (N.code.source || N.code.process) ? (0, a._)`/*# sourceURL=${L} */` : a.nil;
  }
  function w(I, N) {
    if (d(I) && (_(I), $(I))) {
      f(I, N);
      return;
    }
    (0, t.boolOrEmptySchema)(I, N);
  }
  function $({ schema: I, self: N }) {
    if (typeof I == "boolean")
      return !I;
    for (const L in I)
      if (N.RULES.all[L])
        return !0;
    return !1;
  }
  function d(I) {
    return typeof I.schema != "boolean";
  }
  function f(I, N) {
    const { schema: L, gen: V, opts: re } = I;
    re.$comment && L.$comment && O(I), P(I), j(I);
    const ue = V.const("_errs", c.default.errors);
    E(I, ue), V.var(N, (0, a._)`${ue} === ${c.default.errors}`);
  }
  function _(I) {
    (0, l.checkUnknownRules)(I), z(I);
  }
  function E(I, N) {
    if (I.opts.jtd)
      return Ee(I, [], !1, N);
    const L = (0, e.getSchemaTypes)(I.schema), V = (0, e.coerceAndCheckDataType)(I, L);
    Ee(I, L, !V, N);
  }
  function z(I) {
    const { schema: N, errSchemaPath: L, opts: V, self: re } = I;
    N.$ref && V.ignoreKeywordsWithRef && (0, l.schemaHasRulesButRef)(N, re.RULES) && re.logger.warn(`$ref: keywords ignored in schema at path "${L}"`);
  }
  function C(I) {
    const { schema: N, opts: L } = I;
    N.default !== void 0 && L.useDefaults && L.strictSchema && (0, l.checkStrictMode)(I, "default is ignored in the schema root");
  }
  function P(I) {
    const N = I.schema[I.opts.schemaId];
    N && (I.baseId = (0, u.resolveUrl)(I.opts.uriResolver, I.baseId, N));
  }
  function j(I) {
    if (I.schema.$async && !I.schemaEnv.$async)
      throw new Error("async schema in sync schema");
  }
  function O({ gen: I, schemaEnv: N, schema: L, errSchemaPath: V, opts: re }) {
    const ue = L.$comment;
    if (re.$comment === !0)
      I.code((0, a._)`${c.default.self}.logger.log(${ue})`);
    else if (typeof re.$comment == "function") {
      const Ze = (0, a.str)`${V}/$comment`, ht = I.scopeValue("root", { ref: N.root });
      I.code((0, a._)`${c.default.self}.opts.$comment(${ue}, ${Ze}, ${ht}.schema)`);
    }
  }
  function q(I) {
    const { gen: N, schemaEnv: L, validateName: V, ValidationError: re, opts: ue } = I;
    L.$async ? N.if((0, a._)`${c.default.errors} === 0`, () => N.return(c.default.data), () => N.throw((0, a._)`new ${re}(${c.default.vErrors})`)) : (N.assign((0, a._)`${V}.errors`, c.default.vErrors), ue.unevaluated && se(I), N.return((0, a._)`${c.default.errors} === 0`));
  }
  function se({ gen: I, evaluated: N, props: L, items: V }) {
    L instanceof a.Name && I.assign((0, a._)`${N}.props`, L), V instanceof a.Name && I.assign((0, a._)`${N}.items`, V);
  }
  function Ee(I, N, L, V) {
    const { gen: re, schema: ue, data: Ze, allErrors: ht, opts: Ye, self: Xe } = I, { RULES: He } = Xe;
    if (ue.$ref && (Ye.ignoreKeywordsWithRef || !(0, l.schemaHasRulesButRef)(ue, He))) {
      re.block(() => X(I, "$ref", He.all.$ref.definition));
      return;
    }
    Ye.jtd || oe(I, N), re.block(() => {
      for (const ot of He.rules)
        or(ot);
      or(He.post);
    });
    function or(ot) {
      (0, r.shouldUseGroup)(ue, ot) && (ot.type ? (re.if((0, n.checkDataType)(ot.type, Ze, Ye.strictNumbers)), be(I, ot), N.length === 1 && N[0] === ot.type && L && (re.else(), (0, n.reportTypeError)(I)), re.endIf()) : be(I, ot), ht || re.if((0, a._)`${c.default.errors} === ${V || 0}`));
    }
  }
  function be(I, N) {
    const { gen: L, schema: V, opts: { useDefaults: re } } = I;
    re && (0, s.assignDefaults)(I, N.type), L.block(() => {
      for (const ue of N.rules)
        (0, r.shouldUseRule)(V, ue) && X(I, ue.keyword, ue.definition, N.type);
    });
  }
  function oe(I, N) {
    I.schemaEnv.meta || !I.opts.strictTypes || (Me(I, N), I.opts.allowUnionTypes || Z(I, N), A(I, I.dataTypes));
  }
  function Me(I, N) {
    if (N.length) {
      if (!I.dataTypes.length) {
        I.dataTypes = N;
        return;
      }
      N.forEach((L) => {
        x(I.dataTypes, L) || R(I, `type "${L}" not allowed by context "${I.dataTypes.join(",")}"`);
      }), v(I, N);
    }
  }
  function Z(I, N) {
    N.length > 1 && !(N.length === 2 && N.includes("null")) && R(I, "use allowUnionTypes to allow union type keyword");
  }
  function A(I, N) {
    const L = I.self.RULES.all;
    for (const V in L) {
      const re = L[V];
      if (typeof re == "object" && (0, r.shouldUseRule)(I.schema, re)) {
        const { type: ue } = re.definition;
        ue.length && !ue.some((Ze) => D(N, Ze)) && R(I, `missing type "${ue.join(",")}" for keyword "${V}"`);
      }
    }
  }
  function D(I, N) {
    return I.includes(N) || N === "number" && I.includes("integer");
  }
  function x(I, N) {
    return I.includes(N) || N === "integer" && I.includes("number");
  }
  function v(I, N) {
    const L = [];
    for (const V of I.dataTypes)
      x(N, V) ? L.push(V) : N.includes("integer") && V === "number" && L.push("integer");
    I.dataTypes = L;
  }
  function R(I, N) {
    const L = I.schemaEnv.baseId + I.errSchemaPath;
    N += ` at "${L}" (strictTypes)`, (0, l.checkStrictMode)(I, N, I.opts.strictTypes);
  }
  class U {
    constructor(N, L, V) {
      if ((0, i.validateKeywordUsage)(N, L, V), this.gen = N.gen, this.allErrors = N.allErrors, this.keyword = V, this.data = N.data, this.schema = N.schema[V], this.$data = L.$data && N.opts.$data && this.schema && this.schema.$data, this.schemaValue = (0, l.schemaRefOrVal)(N, this.schema, V, this.$data), this.schemaType = L.schemaType, this.parentSchema = N.schema, this.params = {}, this.it = N, this.def = L, this.$data)
        this.schemaCode = N.gen.const("vSchema", he(this.$data, N));
      else if (this.schemaCode = this.schemaValue, !(0, i.validSchemaType)(this.schema, L.schemaType, L.allowUndefined))
        throw new Error(`${V} value must be ${JSON.stringify(L.schemaType)}`);
      ("code" in L ? L.trackErrors : L.errors !== !1) && (this.errsCount = N.gen.const("_errs", c.default.errors));
    }
    result(N, L, V) {
      this.failResult((0, a.not)(N), L, V);
    }
    failResult(N, L, V) {
      this.gen.if(N), V ? V() : this.error(), L ? (this.gen.else(), L(), this.allErrors && this.gen.endIf()) : this.allErrors ? this.gen.endIf() : this.gen.else();
    }
    pass(N, L) {
      this.failResult((0, a.not)(N), void 0, L);
    }
    fail(N) {
      if (N === void 0) {
        this.error(), this.allErrors || this.gen.if(!1);
        return;
      }
      this.gen.if(N), this.error(), this.allErrors ? this.gen.endIf() : this.gen.else();
    }
    fail$data(N) {
      if (!this.$data)
        return this.fail(N);
      const { schemaCode: L } = this;
      this.fail((0, a._)`${L} !== undefined && (${(0, a.or)(this.invalid$data(), N)})`);
    }
    error(N, L, V) {
      if (L) {
        this.setParams(L), this._error(N, V), this.setParams({});
        return;
      }
      this._error(N, V);
    }
    _error(N, L) {
      (N ? h.reportExtraError : h.reportError)(this, this.def.error, L);
    }
    $dataError() {
      (0, h.reportError)(this, this.def.$dataError || h.keyword$DataError);
    }
    reset() {
      if (this.errsCount === void 0)
        throw new Error('add "trackErrors" to keyword definition');
      (0, h.resetErrorsCount)(this.gen, this.errsCount);
    }
    ok(N) {
      this.allErrors || this.gen.if(N);
    }
    setParams(N, L) {
      L ? Object.assign(this.params, N) : this.params = N;
    }
    block$data(N, L, V = a.nil) {
      this.gen.block(() => {
        this.check$data(N, V), L();
      });
    }
    check$data(N = a.nil, L = a.nil) {
      if (!this.$data)
        return;
      const { gen: V, schemaCode: re, schemaType: ue, def: Ze } = this;
      V.if((0, a.or)((0, a._)`${re} === undefined`, L)), N !== a.nil && V.assign(N, !0), (ue.length || Ze.validateSchema) && (V.elseIf(this.invalid$data()), this.$dataError(), N !== a.nil && V.assign(N, !1)), V.else();
    }
    invalid$data() {
      const { gen: N, schemaCode: L, schemaType: V, def: re, it: ue } = this;
      return (0, a.or)(Ze(), ht());
      function Ze() {
        if (V.length) {
          if (!(L instanceof a.Name))
            throw new Error("ajv implementation error");
          const Ye = Array.isArray(V) ? V : [V];
          return (0, a._)`${(0, n.checkDataTypes)(Ye, L, ue.opts.strictNumbers, n.DataType.Wrong)}`;
        }
        return a.nil;
      }
      function ht() {
        if (re.validateSchema) {
          const Ye = N.scopeValue("validate$data", { ref: re.validateSchema });
          return (0, a._)`!${Ye}(${L})`;
        }
        return a.nil;
      }
    }
    subschema(N, L) {
      const V = (0, o.getSubschema)(this.it, N);
      (0, o.extendSubschemaData)(V, this.it, N), (0, o.extendSubschemaMode)(V, N);
      const re = { ...this.it, ...V, items: void 0, props: void 0 };
      return w(re, L), re;
    }
    mergeEvaluated(N, L) {
      const { it: V, gen: re } = this;
      V.opts.unevaluated && (V.props !== !0 && N.props !== void 0 && (V.props = l.mergeEvaluated.props(re, N.props, V.props, L)), V.items !== !0 && N.items !== void 0 && (V.items = l.mergeEvaluated.items(re, N.items, V.items, L)));
    }
    mergeValidEvaluated(N, L) {
      const { it: V, gen: re } = this;
      if (V.opts.unevaluated && (V.props !== !0 || V.items !== !0))
        return re.if(L, () => this.mergeEvaluated(N, a.Name)), !0;
    }
  }
  Tt.KeywordCxt = U;
  function X(I, N, L, V) {
    const re = new U(I, L, N);
    "code" in L ? L.code(re, V) : re.$data && L.validate ? (0, i.funcKeywordCode)(re, L) : "macro" in L ? (0, i.macroKeywordCode)(re, L) : (L.compile || L.validate) && (0, i.funcKeywordCode)(re, L);
  }
  const ne = /^\/(?:[^~]|~0|~1)*$/, ye = /^([0-9]+)(#|\/(?:[^~]|~0|~1)*)?$/;
  function he(I, { dataLevel: N, dataNames: L, dataPathArr: V }) {
    let re, ue;
    if (I === "")
      return c.default.rootData;
    if (I[0] === "/") {
      if (!ne.test(I))
        throw new Error(`Invalid JSON-pointer: ${I}`);
      re = I, ue = c.default.rootData;
    } else {
      const Xe = ye.exec(I);
      if (!Xe)
        throw new Error(`Invalid JSON-pointer: ${I}`);
      const He = +Xe[1];
      if (re = Xe[2], re === "#") {
        if (He >= N)
          throw new Error(Ye("property/index", He));
        return V[N - He];
      }
      if (He > N)
        throw new Error(Ye("data", He));
      if (ue = L[N - He], !re)
        return ue;
    }
    let Ze = ue;
    const ht = re.split("/");
    for (const Xe of ht)
      Xe && (ue = (0, a._)`${ue}${(0, a.getProperty)((0, l.unescapeJsonPointer)(Xe))}`, Ze = (0, a._)`${Ze} && ${ue}`);
    return Ze;
    function Ye(Xe, He) {
      return `Cannot access ${Xe} ${He} levels up, current level is ${N}`;
    }
  }
  return Tt.getData = he, Tt;
}
var Cn = {}, ou;
function _a() {
  if (ou) return Cn;
  ou = 1, Object.defineProperty(Cn, "__esModule", { value: !0 });
  class t extends Error {
    constructor(r) {
      super("validation failed"), this.errors = r, this.ajv = this.validation = !0;
    }
  }
  return Cn.default = t, Cn;
}
var On = {}, au;
function oi() {
  if (au) return On;
  au = 1, Object.defineProperty(On, "__esModule", { value: !0 });
  const t = /* @__PURE__ */ si();
  class e extends Error {
    constructor(n, s, i, o) {
      super(o || `can't resolve reference ${i} from id ${s}`), this.missingRef = (0, t.resolveUrl)(n, s, i), this.missingSchema = (0, t.normalizeId)((0, t.getFullPath)(n, this.missingRef));
    }
  }
  return On.default = e, On;
}
var rt = {}, cu;
function ya() {
  if (cu) return rt;
  cu = 1, Object.defineProperty(rt, "__esModule", { value: !0 }), rt.resolveSchema = rt.getCompilingSchema = rt.resolveRef = rt.compileSchema = rt.SchemaEnv = void 0;
  const t = /* @__PURE__ */ ce(), e = /* @__PURE__ */ _a(), r = /* @__PURE__ */ Vt(), n = /* @__PURE__ */ si(), s = /* @__PURE__ */ fe(), i = /* @__PURE__ */ ii();
  class o {
    constructor(b) {
      var m;
      this.refs = {}, this.dynamicAnchors = {};
      let w;
      typeof b.schema == "object" && (w = b.schema), this.schema = b.schema, this.schemaId = b.schemaId, this.root = b.root || this, this.baseId = (m = b.baseId) !== null && m !== void 0 ? m : (0, n.normalizeId)(w?.[b.schemaId || "$id"]), this.schemaPath = b.schemaPath, this.localRefs = b.localRefs, this.meta = b.meta, this.$async = w?.$async, this.refs = {};
    }
  }
  rt.SchemaEnv = o;
  function a(y) {
    const b = l.call(this, y);
    if (b)
      return b;
    const m = (0, n.getFullPath)(this.opts.uriResolver, y.root.baseId), { es5: w, lines: $ } = this.opts.code, { ownProperties: d } = this.opts, f = new t.CodeGen(this.scope, { es5: w, lines: $, ownProperties: d });
    let _;
    y.$async && (_ = f.scopeValue("Error", {
      ref: e.default,
      code: (0, t._)`require("ajv/dist/runtime/validation_error").default`
    }));
    const E = f.scopeName("validate");
    y.validateName = E;
    const z = {
      gen: f,
      allErrors: this.opts.allErrors,
      data: r.default.data,
      parentData: r.default.parentData,
      parentDataProperty: r.default.parentDataProperty,
      dataNames: [r.default.data],
      dataPathArr: [t.nil],
      // TODO can its length be used as dataLevel if nil is removed?
      dataLevel: 0,
      dataTypes: [],
      definedProperties: /* @__PURE__ */ new Set(),
      topSchemaRef: f.scopeValue("schema", this.opts.code.source === !0 ? { ref: y.schema, code: (0, t.stringify)(y.schema) } : { ref: y.schema }),
      validateName: E,
      ValidationError: _,
      schema: y.schema,
      schemaEnv: y,
      rootId: m,
      baseId: y.baseId || m,
      schemaPath: t.nil,
      errSchemaPath: y.schemaPath || (this.opts.jtd ? "" : "#"),
      errorPath: (0, t._)`""`,
      opts: this.opts,
      self: this
    };
    let C;
    try {
      this._compilations.add(y), (0, i.validateFunctionCode)(z), f.optimize(this.opts.code.optimize);
      const P = f.toString();
      C = `${f.scopeRefs(r.default.scope)}return ${P}`, this.opts.code.process && (C = this.opts.code.process(C, y));
      const O = new Function(`${r.default.self}`, `${r.default.scope}`, C)(this, this.scope.get());
      if (this.scope.value(E, { ref: O }), O.errors = null, O.schema = y.schema, O.schemaEnv = y, y.$async && (O.$async = !0), this.opts.code.source === !0 && (O.source = { validateName: E, validateCode: P, scopeValues: f._values }), this.opts.unevaluated) {
        const { props: q, items: se } = z;
        O.evaluated = {
          props: q instanceof t.Name ? void 0 : q,
          items: se instanceof t.Name ? void 0 : se,
          dynamicProps: q instanceof t.Name,
          dynamicItems: se instanceof t.Name
        }, O.source && (O.source.evaluated = (0, t.stringify)(O.evaluated));
      }
      return y.validate = O, y;
    } catch (P) {
      throw delete y.validate, delete y.validateName, C && this.logger.error("Error compiling schema, function code:", C), P;
    } finally {
      this._compilations.delete(y);
    }
  }
  rt.compileSchema = a;
  function c(y, b, m) {
    var w;
    m = (0, n.resolveUrl)(this.opts.uriResolver, b, m);
    const $ = y.refs[m];
    if ($)
      return $;
    let d = p.call(this, y, m);
    if (d === void 0) {
      const f = (w = y.localRefs) === null || w === void 0 ? void 0 : w[m], { schemaId: _ } = this.opts;
      f && (d = new o({ schema: f, schemaId: _, root: y, baseId: b }));
    }
    if (d !== void 0)
      return y.refs[m] = u.call(this, d);
  }
  rt.resolveRef = c;
  function u(y) {
    return (0, n.inlineRef)(y.schema, this.opts.inlineRefs) ? y.schema : y.validate ? y : a.call(this, y);
  }
  function l(y) {
    for (const b of this._compilations)
      if (h(b, y))
        return b;
  }
  rt.getCompilingSchema = l;
  function h(y, b) {
    return y.schema === b.schema && y.root === b.root && y.baseId === b.baseId;
  }
  function p(y, b) {
    let m;
    for (; typeof (m = this.refs[b]) == "string"; )
      b = m;
    return m || this.schemas[b] || g.call(this, y, b);
  }
  function g(y, b) {
    const m = this.opts.uriResolver.parse(b), w = (0, n._getFullPath)(this.opts.uriResolver, m);
    let $ = (0, n.getFullPath)(this.opts.uriResolver, y.baseId, void 0);
    if (Object.keys(y.schema).length > 0 && w === $)
      return k.call(this, m, y);
    const d = (0, n.normalizeId)(w), f = this.refs[d] || this.schemas[d];
    if (typeof f == "string") {
      const _ = g.call(this, y, f);
      return typeof _?.schema != "object" ? void 0 : k.call(this, m, _);
    }
    if (typeof f?.schema == "object") {
      if (f.validate || a.call(this, f), d === (0, n.normalizeId)(b)) {
        const { schema: _ } = f, { schemaId: E } = this.opts, z = _[E];
        return z && ($ = (0, n.resolveUrl)(this.opts.uriResolver, $, z)), new o({ schema: _, schemaId: E, root: y, baseId: $ });
      }
      return k.call(this, m, f);
    }
  }
  rt.resolveSchema = g;
  const S = /* @__PURE__ */ new Set([
    "properties",
    "patternProperties",
    "enum",
    "dependencies",
    "definitions"
  ]);
  function k(y, { baseId: b, schema: m, root: w }) {
    var $;
    if ((($ = y.fragment) === null || $ === void 0 ? void 0 : $[0]) !== "/")
      return;
    for (const _ of y.fragment.slice(1).split("/")) {
      if (typeof m == "boolean")
        return;
      const E = m[(0, s.unescapeFragment)(_)];
      if (E === void 0)
        return;
      m = E;
      const z = typeof m == "object" && m[this.opts.schemaId];
      !S.has(_) && z && (b = (0, n.resolveUrl)(this.opts.uriResolver, b, z));
    }
    let d;
    if (typeof m != "boolean" && m.$ref && !(0, s.schemaHasRulesButRef)(m, this.RULES)) {
      const _ = (0, n.resolveUrl)(this.opts.uriResolver, b, m.$ref);
      d = g.call(this, w, _);
    }
    const { schemaId: f } = this.opts;
    if (d = d || new o({ schema: m, schemaId: f, root: w, baseId: b }), d.schema !== d.root.schema)
      return d;
  }
  return rt;
}
const Zb = "https://raw.githubusercontent.com/ajv-validator/ajv/master/lib/refs/data.json#", Hb = "Meta-schema for $data reference (JSON AnySchema extension proposal)", Fb = "object", Vb = ["$data"], Bb = { $data: { type: "string", anyOf: [{ format: "relative-json-pointer" }, { format: "json-pointer" }] } }, Wb = !1, Gb = {
  $id: Zb,
  description: Hb,
  type: Fb,
  required: Vb,
  properties: Bb,
  additionalProperties: Wb
};
var An = {}, Hr = { exports: {} }, Ai, uu;
function qh() {
  if (uu) return Ai;
  uu = 1;
  const t = RegExp.prototype.test.bind(/^[\da-f]{8}-[\da-f]{4}-[\da-f]{4}-[\da-f]{4}-[\da-f]{12}$/iu), e = RegExp.prototype.test.bind(/^(?:(?:25[0-5]|2[0-4]\d|1\d{2}|[1-9]\d|\d)\.){3}(?:25[0-5]|2[0-4]\d|1\d{2}|[1-9]\d|\d)$/u), r = RegExp.prototype.test.bind(/^[\da-f]{2}$/iu), n = RegExp.prototype.test.bind(/^[\da-z\-._~]$/iu), s = RegExp.prototype.test.bind(/^[\da-z\-._~!$&'()*+,;=:@/]$/iu);
  function i(d) {
    let f = "", _ = 0, E = 0;
    for (E = 0; E < d.length; E++)
      if (_ = d[E].charCodeAt(0), _ !== 48) {
        if (!(_ >= 48 && _ <= 57 || _ >= 65 && _ <= 70 || _ >= 97 && _ <= 102))
          return "";
        f += d[E];
        break;
      }
    for (E += 1; E < d.length; E++) {
      if (_ = d[E].charCodeAt(0), !(_ >= 48 && _ <= 57 || _ >= 65 && _ <= 70 || _ >= 97 && _ <= 102))
        return "";
      f += d[E];
    }
    return f;
  }
  const o = RegExp.prototype.test.bind(/[^!"$&'()*+,\-.;=_`a-z{}~]/u);
  function a(d) {
    return d.length = 0, !0;
  }
  function c(d, f, _) {
    if (d.length) {
      const E = i(d);
      if (E !== "")
        f.push(E);
      else
        return _.error = !0, !1;
      d.length = 0;
    }
    return !0;
  }
  function u(d) {
    let f = 0;
    const _ = { error: !1, address: "", zone: "" }, E = [], z = [];
    let C = !1, P = !1, j = c;
    for (let O = 0; O < d.length; O++) {
      const q = d[O];
      if (!(q === "[" || q === "]"))
        if (q === ":") {
          if (C === !0 && (P = !0), !j(z, E, _))
            break;
          if (++f > 7) {
            _.error = !0;
            break;
          }
          O > 0 && d[O - 1] === ":" && (C = !0), E.push(":");
          continue;
        } else if (q === "%") {
          if (!j(z, E, _))
            break;
          j = a;
        } else {
          z.push(q);
          continue;
        }
    }
    return z.length && (j === a ? _.zone = z.join("") : P ? E.push(z.join("")) : E.push(i(z))), _.address = E.join(""), _;
  }
  function l(d) {
    if (h(d, ":") < 2)
      return { host: d, isIPV6: !1 };
    const f = u(d);
    if (f.error)
      return { host: d, isIPV6: !1 };
    {
      let _ = f.address, E = f.address;
      return f.zone && (_ += "%" + f.zone, E += "%25" + f.zone), { host: _, isIPV6: !0, escapedHost: E };
    }
  }
  function h(d, f) {
    let _ = 0;
    for (let E = 0; E < d.length; E++)
      d[E] === f && _++;
    return _;
  }
  function p(d) {
    let f = d;
    const _ = [];
    let E = -1, z = 0;
    for (; z = f.length; ) {
      if (z === 1) {
        if (f === ".")
          break;
        if (f === "/") {
          _.push("/");
          break;
        } else {
          _.push(f);
          break;
        }
      } else if (z === 2) {
        if (f[0] === ".") {
          if (f[1] === ".")
            break;
          if (f[1] === "/") {
            f = f.slice(2);
            continue;
          }
        } else if (f[0] === "/" && (f[1] === "." || f[1] === "/")) {
          _.push("/");
          break;
        }
      } else if (z === 3 && f === "/..") {
        _.length !== 0 && _.pop(), _.push("/");
        break;
      }
      if (f[0] === ".") {
        if (f[1] === ".") {
          if (f[2] === "/") {
            f = f.slice(3);
            continue;
          }
        } else if (f[1] === "/") {
          f = f.slice(2);
          continue;
        }
      } else if (f[0] === "/" && f[1] === ".") {
        if (f[2] === "/") {
          f = f.slice(2);
          continue;
        } else if (f[2] === "." && f[3] === "/") {
          f = f.slice(3), _.length !== 0 && _.pop();
          continue;
        }
      }
      if ((E = f.indexOf("/", 1)) === -1) {
        _.push(f);
        break;
      } else
        _.push(f.slice(0, E)), f = f.slice(E);
    }
    return _.join("");
  }
  const g = { "@": "%40", "/": "%2F", "?": "%3F", "#": "%23", ":": "%3A" }, S = /[@/?#:]/g, k = /[@/?#]/g;
  function y(d, f) {
    const _ = f ? k : S;
    return _.lastIndex = 0, d.replace(_, (E) => g[E]);
  }
  function b(d, f = !1) {
    if (d.indexOf("%") === -1)
      return d;
    let _ = "";
    for (let E = 0; E < d.length; E++) {
      if (d[E] === "%" && E + 2 < d.length) {
        const z = d.slice(E + 1, E + 3);
        if (r(z)) {
          const C = z.toUpperCase(), P = String.fromCharCode(parseInt(C, 16));
          f && n(P) ? _ += P : _ += "%" + C, E += 2;
          continue;
        }
      }
      _ += d[E];
    }
    return _;
  }
  function m(d) {
    let f = "";
    for (let _ = 0; _ < d.length; _++) {
      if (d[_] === "%" && _ + 2 < d.length) {
        const E = d.slice(_ + 1, _ + 3);
        if (r(E)) {
          const z = E.toUpperCase(), C = String.fromCharCode(parseInt(z, 16));
          C !== "." && n(C) ? f += C : f += "%" + z, _ += 2;
          continue;
        }
      }
      s(d[_]) ? f += d[_] : f += escape(d[_]);
    }
    return f;
  }
  function w(d) {
    let f = "";
    for (let _ = 0; _ < d.length; _++) {
      if (d[_] === "%" && _ + 2 < d.length) {
        const E = d.slice(_ + 1, _ + 3);
        if (r(E)) {
          f += "%" + E.toUpperCase(), _ += 2;
          continue;
        }
      }
      f += escape(d[_]);
    }
    return f;
  }
  function $(d) {
    const f = [];
    if (d.userinfo !== void 0 && (f.push(d.userinfo), f.push("@")), d.host !== void 0) {
      let _ = unescape(d.host);
      if (!e(_)) {
        const E = l(_);
        E.isIPV6 === !0 ? _ = `[${E.escapedHost}]` : _ = y(_, !1);
      }
      f.push(_);
    }
    return (typeof d.port == "number" || typeof d.port == "string") && (f.push(":"), f.push(String(d.port))), f.length ? f.join("") : void 0;
  }
  return Ai = {
    nonSimpleDomain: o,
    recomposeAuthority: $,
    reescapeHostDelimiters: y,
    normalizePercentEncoding: b,
    normalizePathEncoding: m,
    escapePreservingEscapes: w,
    removeDotSegments: p,
    isIPv4: e,
    isUUID: t,
    normalizeIPv6: l,
    stringArrayToHexStripped: i
  }, Ai;
}
var Ni, lu;
function Jb() {
  if (lu) return Ni;
  lu = 1;
  const { isUUID: t } = qh(), e = /([\da-z][\d\-a-z]{0,31}):((?:[\w!$'()*+,\-.:;=@]|%[\da-f]{2})+)/iu, r = (
    /** @type {const} */
    [
      "http",
      "https",
      "ws",
      "wss",
      "urn",
      "urn:uuid"
    ]
  );
  function n(d) {
    return r.indexOf(
      /** @type {*} */
      d
    ) !== -1;
  }
  function s(d) {
    return d.secure === !0 ? !0 : d.secure === !1 ? !1 : d.scheme ? d.scheme.length === 3 && (d.scheme[0] === "w" || d.scheme[0] === "W") && (d.scheme[1] === "s" || d.scheme[1] === "S") && (d.scheme[2] === "s" || d.scheme[2] === "S") : !1;
  }
  function i(d) {
    return d.host || (d.error = d.error || "HTTP URIs must have a host."), d;
  }
  function o(d) {
    const f = String(d.scheme).toLowerCase() === "https";
    return (d.port === (f ? 443 : 80) || d.port === "") && (d.port = void 0), d.path || (d.path = "/"), d;
  }
  function a(d) {
    return d.secure = s(d), d.resourceName = (d.path || "/") + (d.query ? "?" + d.query : ""), d.path = void 0, d.query = void 0, d;
  }
  function c(d) {
    if ((d.port === (s(d) ? 443 : 80) || d.port === "") && (d.port = void 0), typeof d.secure == "boolean" && (d.scheme = d.secure ? "wss" : "ws", d.secure = void 0), d.resourceName) {
      const [f, _] = d.resourceName.split("?");
      d.path = f && f !== "/" ? f : void 0, d.query = _, d.resourceName = void 0;
    }
    return d.fragment = void 0, d;
  }
  function u(d, f) {
    if (!d.path)
      return d.error = "URN can not be parsed", d;
    const _ = d.path.match(e);
    if (_) {
      const E = f.scheme || d.scheme || "urn";
      d.nid = _[1].toLowerCase(), d.nss = _[2];
      const z = `${E}:${f.nid || d.nid}`, C = $(z);
      d.path = void 0, C && (d = C.parse(d, f));
    } else
      d.error = d.error || "URN can not be parsed.";
    return d;
  }
  function l(d, f) {
    if (d.nid === void 0)
      throw new Error("URN without nid cannot be serialized");
    const _ = f.scheme || d.scheme || "urn", E = d.nid.toLowerCase(), z = `${_}:${f.nid || E}`, C = $(z);
    C && (d = C.serialize(d, f));
    const P = d, j = d.nss;
    return P.path = `${E || f.nid}:${j}`, f.skipEscape = !0, P;
  }
  function h(d, f) {
    const _ = d;
    return _.uuid = _.nss, _.nss = void 0, !f.tolerant && (!_.uuid || !t(_.uuid)) && (_.error = _.error || "UUID is not valid."), _;
  }
  function p(d) {
    const f = d;
    return f.nss = (d.uuid || "").toLowerCase(), f;
  }
  const g = (
    /** @type {SchemeHandler} */
    {
      scheme: "http",
      domainHost: !0,
      parse: i,
      serialize: o
    }
  ), S = (
    /** @type {SchemeHandler} */
    {
      scheme: "https",
      domainHost: g.domainHost,
      parse: i,
      serialize: o
    }
  ), k = (
    /** @type {SchemeHandler} */
    {
      scheme: "ws",
      domainHost: !0,
      parse: a,
      serialize: c
    }
  ), y = (
    /** @type {SchemeHandler} */
    {
      scheme: "wss",
      domainHost: k.domainHost,
      parse: k.parse,
      serialize: k.serialize
    }
  ), w = (
    /** @type {Record<SchemeName, SchemeHandler>} */
    {
      http: g,
      https: S,
      ws: k,
      wss: y,
      urn: (
        /** @type {SchemeHandler} */
        {
          scheme: "urn",
          parse: u,
          serialize: l,
          skipNormalize: !0
        }
      ),
      "urn:uuid": (
        /** @type {SchemeHandler} */
        {
          scheme: "urn:uuid",
          parse: h,
          serialize: p,
          skipNormalize: !0
        }
      )
    }
  );
  Object.setPrototypeOf(w, null);
  function $(d) {
    return d && (w[
      /** @type {SchemeName} */
      d
    ] || w[
      /** @type {SchemeName} */
      d.toLowerCase()
    ]) || void 0;
  }
  return Ni = {
    wsIsSecure: s,
    SCHEMES: w,
    isValidSchemeName: n,
    getSchemeHandler: $
  }, Ni;
}
var du;
function Kb() {
  if (du) return Hr.exports;
  du = 1;
  const { normalizeIPv6: t, removeDotSegments: e, recomposeAuthority: r, normalizePercentEncoding: n, normalizePathEncoding: s, escapePreservingEscapes: i, reescapeHostDelimiters: o, isIPv4: a, nonSimpleDomain: c } = qh(), { SCHEMES: u, getSchemeHandler: l } = Jb();
  function h(C, P) {
    return typeof C == "string" ? C = /** @type {T} */
    f(C, P) : typeof C == "object" && (C = /** @type {T} */
    d(k(C, P), P)), C;
  }
  function p(C, P, j) {
    const O = j ? Object.assign({ scheme: "null" }, j) : { scheme: "null" }, { parsed: q, malformedAuthorityOrPort: se } = $(C, O), { parsed: Ee, malformedAuthorityOrPort: be } = $(P, O);
    if (se || be)
      throw new Error(q.error || Ee.error || "URI is malformed.");
    const oe = g(q, Ee, O, !0);
    return O.skipEscape = !0, k(oe, O);
  }
  function g(C, P, j, O) {
    const q = {};
    return O || (C = d(k(C, j), j), P = d(k(P, j), j)), j = j || {}, !j.tolerant && P.scheme ? (q.scheme = P.scheme, q.userinfo = P.userinfo, q.host = P.host, q.port = P.port, q.path = e(P.path || ""), q.query = P.query) : (P.userinfo !== void 0 || P.host !== void 0 || P.port !== void 0 ? (q.userinfo = P.userinfo, q.host = P.host, q.port = P.port, q.path = e(P.path || ""), q.query = P.query) : (P.path ? (P.path[0] === "/" ? q.path = e(P.path) : ((C.userinfo !== void 0 || C.host !== void 0 || C.port !== void 0) && !C.path ? q.path = "/" + P.path : C.path ? q.path = C.path.slice(0, C.path.lastIndexOf("/") + 1) + P.path : q.path = P.path, q.path = e(q.path)), q.query = P.query) : (q.path = C.path, P.query !== void 0 ? q.query = P.query : q.query = C.query), q.userinfo = C.userinfo, q.host = C.host, q.port = C.port), q.scheme = C.scheme), q.fragment = P.fragment, q;
  }
  function S(C, P, j) {
    const O = E(C, j), q = E(P, j);
    return O !== void 0 && q !== void 0 && O.toLowerCase() === q.toLowerCase();
  }
  function k(C, P) {
    const j = {
      host: C.host,
      scheme: C.scheme,
      userinfo: C.userinfo,
      port: C.port,
      path: C.path,
      query: C.query,
      nid: C.nid,
      nss: C.nss,
      uuid: C.uuid,
      fragment: C.fragment,
      reference: C.reference,
      resourceName: C.resourceName,
      secure: C.secure,
      error: ""
    }, O = Object.assign({}, P), q = [], se = l(O.scheme || j.scheme);
    se && se.serialize && se.serialize(j, O), j.path !== void 0 && (O.skipEscape ? j.path = n(j.path) : (j.path = i(j.path), j.scheme !== void 0 && (j.path = j.path.split("%3A").join(":")))), O.reference !== "suffix" && j.scheme && q.push(j.scheme, ":");
    const Ee = r(j);
    if (Ee !== void 0 && (O.reference !== "suffix" && q.push("//"), q.push(Ee), j.path && j.path[0] !== "/" && q.push("/")), j.path !== void 0) {
      let be = j.path;
      !O.absolutePath && (!se || !se.absolutePath) && (be = e(be)), Ee === void 0 && be[0] === "/" && be[1] === "/" && (be = "/%2F" + be.slice(2)), q.push(be);
    }
    return j.query !== void 0 && q.push("?", j.query), j.fragment !== void 0 && q.push("#", j.fragment), q.join("");
  }
  const y = /^(?:([^#/:?]+):)?(?:\/\/((?:([^#/?@]*)@)?(\[[^#/?\]]+\]|[^#/:?]*)(?::(\d*))?))?([^#?]*)(?:\?([^#]*))?(?:#((?:.|[\n\r])*))?/u, b = /^(?:[^#/:?]+:)?\/\/([^/?#]*)/, m = /^(?:[^#/:?]+:)?([/\\\t\n\r]*)/;
  function w(C, P) {
    if (P[2] !== void 0 && C.path && C.path[0] !== "/")
      return 'URI path must start with "/" when authority is present.';
    if (typeof C.port == "number" && (C.port < 0 || C.port > 65535))
      return "URI port is malformed.";
  }
  function $(C, P) {
    const j = Object.assign({}, P), O = {
      scheme: void 0,
      userinfo: void 0,
      host: "",
      port: void 0,
      path: "",
      query: void 0,
      fragment: void 0
    };
    let q = !1, se = !1;
    j.reference === "suffix" && (j.scheme ? C = j.scheme + ":" + C : C = "//" + C);
    const Ee = C.match(b);
    Ee !== null && Ee[1].indexOf("\\") !== -1 && (O.error = "URI authority must not contain a literal backslash.", q = !0);
    const be = C.match(m);
    if (be !== null) {
      const Me = be[1], Z = Me.replace(/[\t\n\r]/g, "");
      Z.length >= 2 && (Z.slice(0, 2) !== "//" ? (O.error = O.error || "URI authority must not contain a literal backslash.", q = !0) : Me.length !== Z.length && (O.error = O.error || "URI authority introducer must not contain whitespace.", q = !0));
    }
    const oe = C.match(y);
    if (oe) {
      O.scheme = oe[1], O.userinfo = oe[3], O.host = oe[4], O.port = parseInt(oe[5], 10), O.path = oe[6] || "", O.query = oe[7], O.fragment = oe[8], isNaN(O.port) && (O.port = oe[5]);
      const Me = w(O, oe);
      if (Me !== void 0 && (O.error = O.error || Me, q = !0), O.host)
        if (a(O.host) === !1) {
          const D = t(O.host);
          O.host = D.host.toLowerCase(), se = D.isIPV6;
        } else
          se = !0;
      O.scheme === void 0 && O.userinfo === void 0 && O.host === void 0 && O.port === void 0 && O.query === void 0 && !O.path ? O.reference = "same-document" : O.scheme === void 0 ? O.reference = "relative" : O.fragment === void 0 ? O.reference = "absolute" : O.reference = "uri", j.reference && j.reference !== "suffix" && j.reference !== O.reference && (O.error = O.error || "URI is not a " + j.reference + " reference.");
      const Z = l(j.scheme || O.scheme);
      if (!j.unicodeSupport && (!Z || !Z.unicodeSupport) && O.host && (j.domainHost || Z && Z.domainHost) && se === !1 && c(O.host))
        try {
          O.host = new URL("http://" + O.host).hostname;
        } catch (A) {
          O.error = O.error || "Host's domain name can not be converted to ASCII: " + A;
        }
      if ((!Z || Z && !Z.skipNormalize) && (C.indexOf("%") !== -1 && (O.scheme !== void 0 && (O.scheme = unescape(O.scheme)), O.host !== void 0 && (O.host = o(unescape(O.host), se))), O.path && (O.path = s(O.path)), O.fragment))
        try {
          O.fragment = encodeURI(decodeURIComponent(O.fragment));
        } catch {
          O.error = O.error || "URI malformed";
        }
      Z && Z.parse && Z.parse(O, j);
    } else
      O.error = O.error || "URI can not be parsed.";
    return { parsed: O, malformedAuthorityOrPort: q };
  }
  function d(C, P) {
    return $(C, P).parsed;
  }
  function f(C, P) {
    return _(C, P).normalized;
  }
  function _(C, P) {
    const { parsed: j, malformedAuthorityOrPort: O } = $(C, P);
    return {
      normalized: O ? C : k(j, P),
      malformedAuthorityOrPort: O
    };
  }
  function E(C, P) {
    if (typeof C == "string") {
      const { normalized: j, malformedAuthorityOrPort: O } = _(C, P);
      return O ? void 0 : j;
    }
    if (typeof C == "object")
      return k(C, P);
  }
  const z = {
    SCHEMES: u,
    normalize: h,
    resolve: p,
    resolveComponent: g,
    equal: S,
    serialize: k,
    parse: d
  };
  return Hr.exports = z, Hr.exports.default = z, Hr.exports.fastUri = z, Hr.exports;
}
var hu;
function Qb() {
  if (hu) return An;
  hu = 1, Object.defineProperty(An, "__esModule", { value: !0 });
  const t = Kb();
  return t.code = 'require("ajv/dist/runtime/uri").default', An.default = t, An;
}
var fu;
function Yb() {
  return fu || (fu = 1, (function(t) {
    Object.defineProperty(t, "__esModule", { value: !0 }), t.CodeGen = t.Name = t.nil = t.stringify = t.str = t._ = t.KeywordCxt = void 0;
    var e = /* @__PURE__ */ ii();
    Object.defineProperty(t, "KeywordCxt", { enumerable: !0, get: function() {
      return e.KeywordCxt;
    } });
    var r = /* @__PURE__ */ ce();
    Object.defineProperty(t, "_", { enumerable: !0, get: function() {
      return r._;
    } }), Object.defineProperty(t, "str", { enumerable: !0, get: function() {
      return r.str;
    } }), Object.defineProperty(t, "stringify", { enumerable: !0, get: function() {
      return r.stringify;
    } }), Object.defineProperty(t, "nil", { enumerable: !0, get: function() {
      return r.nil;
    } }), Object.defineProperty(t, "Name", { enumerable: !0, get: function() {
      return r.Name;
    } }), Object.defineProperty(t, "CodeGen", { enumerable: !0, get: function() {
      return r.CodeGen;
    } });
    const n = /* @__PURE__ */ _a(), s = /* @__PURE__ */ oi(), i = /* @__PURE__ */ zh(), o = /* @__PURE__ */ ya(), a = /* @__PURE__ */ ce(), c = /* @__PURE__ */ si(), u = /* @__PURE__ */ Us(), l = /* @__PURE__ */ fe(), h = Gb, p = /* @__PURE__ */ Qb(), g = (Z, A) => new RegExp(Z, A);
    g.code = "new RegExp";
    const S = ["removeAdditional", "useDefaults", "coerceTypes"], k = /* @__PURE__ */ new Set([
      "validate",
      "serialize",
      "parse",
      "wrapper",
      "root",
      "schema",
      "keyword",
      "pattern",
      "formats",
      "validate$data",
      "func",
      "obj",
      "Error"
    ]), y = {
      errorDataPath: "",
      format: "`validateFormats: false` can be used instead.",
      nullable: '"nullable" keyword is supported by default.',
      jsonPointers: "Deprecated jsPropertySyntax can be used instead.",
      extendRefs: "Deprecated ignoreKeywordsWithRef can be used instead.",
      missingRefs: "Pass empty schema with $id that should be ignored to ajv.addSchema.",
      processCode: "Use option `code: {process: (code, schemaEnv: object) => string}`",
      sourceCode: "Use option `code: {source: true}`",
      strictDefaults: "It is default now, see option `strict`.",
      strictKeywords: "It is default now, see option `strict`.",
      uniqueItems: '"uniqueItems" keyword is always validated.',
      unknownFormats: "Disable strict mode or pass `true` to `ajv.addFormat` (or `formats` option).",
      cache: "Map is used as cache, schema object as key.",
      serialize: "Map is used as cache, schema object as key.",
      ajvErrors: "It is default now."
    }, b = {
      ignoreKeywordsWithRef: "",
      jsPropertySyntax: "",
      unicode: '"minLength"/"maxLength" account for unicode characters by default.'
    }, m = 200;
    function w(Z) {
      var A, D, x, v, R, U, X, ne, ye, he, I, N, L, V, re, ue, Ze, ht, Ye, Xe, He, or, ot, ai, ci;
      const qr = Z.strict, ui = (A = Z.code) === null || A === void 0 ? void 0 : A.optimize, ka = ui === !0 || ui === void 0 ? 1 : ui || 0, $a = (x = (D = Z.code) === null || D === void 0 ? void 0 : D.regExp) !== null && x !== void 0 ? x : g, rf = (v = Z.uriResolver) !== null && v !== void 0 ? v : p.default;
      return {
        strictSchema: (U = (R = Z.strictSchema) !== null && R !== void 0 ? R : qr) !== null && U !== void 0 ? U : !0,
        strictNumbers: (ne = (X = Z.strictNumbers) !== null && X !== void 0 ? X : qr) !== null && ne !== void 0 ? ne : !0,
        strictTypes: (he = (ye = Z.strictTypes) !== null && ye !== void 0 ? ye : qr) !== null && he !== void 0 ? he : "log",
        strictTuples: (N = (I = Z.strictTuples) !== null && I !== void 0 ? I : qr) !== null && N !== void 0 ? N : "log",
        strictRequired: (V = (L = Z.strictRequired) !== null && L !== void 0 ? L : qr) !== null && V !== void 0 ? V : !1,
        code: Z.code ? { ...Z.code, optimize: ka, regExp: $a } : { optimize: ka, regExp: $a },
        loopRequired: (re = Z.loopRequired) !== null && re !== void 0 ? re : m,
        loopEnum: (ue = Z.loopEnum) !== null && ue !== void 0 ? ue : m,
        meta: (Ze = Z.meta) !== null && Ze !== void 0 ? Ze : !0,
        messages: (ht = Z.messages) !== null && ht !== void 0 ? ht : !0,
        inlineRefs: (Ye = Z.inlineRefs) !== null && Ye !== void 0 ? Ye : !0,
        schemaId: (Xe = Z.schemaId) !== null && Xe !== void 0 ? Xe : "$id",
        addUsedSchema: (He = Z.addUsedSchema) !== null && He !== void 0 ? He : !0,
        validateSchema: (or = Z.validateSchema) !== null && or !== void 0 ? or : !0,
        validateFormats: (ot = Z.validateFormats) !== null && ot !== void 0 ? ot : !0,
        unicodeRegExp: (ai = Z.unicodeRegExp) !== null && ai !== void 0 ? ai : !0,
        int32range: (ci = Z.int32range) !== null && ci !== void 0 ? ci : !0,
        uriResolver: rf
      };
    }
    class $ {
      constructor(A = {}) {
        this.schemas = {}, this.refs = {}, this.formats = /* @__PURE__ */ Object.create(null), this._compilations = /* @__PURE__ */ new Set(), this._loading = {}, this._cache = /* @__PURE__ */ new Map(), A = this.opts = { ...A, ...w(A) };
        const { es5: D, lines: x } = this.opts.code;
        this.scope = new a.ValueScope({ scope: {}, prefixes: k, es5: D, lines: x }), this.logger = j(A.logger);
        const v = A.validateFormats;
        A.validateFormats = !1, this.RULES = (0, i.getRules)(), d.call(this, y, A, "NOT SUPPORTED"), d.call(this, b, A, "DEPRECATED", "warn"), this._metaOpts = C.call(this), A.formats && E.call(this), this._addVocabularies(), this._addDefaultMetaSchema(), A.keywords && z.call(this, A.keywords), typeof A.meta == "object" && this.addMetaSchema(A.meta), _.call(this), A.validateFormats = v;
      }
      _addVocabularies() {
        this.addKeyword("$async");
      }
      _addDefaultMetaSchema() {
        const { $data: A, meta: D, schemaId: x } = this.opts;
        let v = h;
        x === "id" && (v = { ...h }, v.id = v.$id, delete v.$id), D && A && this.addMetaSchema(v, v[x], !1);
      }
      defaultMeta() {
        const { meta: A, schemaId: D } = this.opts;
        return this.opts.defaultMeta = typeof A == "object" ? A[D] || A : void 0;
      }
      validate(A, D) {
        let x;
        if (typeof A == "string") {
          if (x = this.getSchema(A), !x)
            throw new Error(`no schema with key or ref "${A}"`);
        } else
          x = this.compile(A);
        const v = x(D);
        return "$async" in x || (this.errors = x.errors), v;
      }
      compile(A, D) {
        const x = this._addSchema(A, D);
        return x.validate || this._compileSchemaEnv(x);
      }
      compileAsync(A, D) {
        if (typeof this.opts.loadSchema != "function")
          throw new Error("options.loadSchema should be a function");
        const { loadSchema: x } = this.opts;
        return v.call(this, A, D);
        async function v(he, I) {
          await R.call(this, he.$schema);
          const N = this._addSchema(he, I);
          return N.validate || U.call(this, N);
        }
        async function R(he) {
          he && !this.getSchema(he) && await v.call(this, { $ref: he }, !0);
        }
        async function U(he) {
          try {
            return this._compileSchemaEnv(he);
          } catch (I) {
            if (!(I instanceof s.default))
              throw I;
            return X.call(this, I), await ne.call(this, I.missingSchema), U.call(this, he);
          }
        }
        function X({ missingSchema: he, missingRef: I }) {
          if (this.refs[he])
            throw new Error(`AnySchema ${he} is loaded but ${I} cannot be resolved`);
        }
        async function ne(he) {
          const I = await ye.call(this, he);
          this.refs[he] || await R.call(this, I.$schema), this.refs[he] || this.addSchema(I, he, D);
        }
        async function ye(he) {
          const I = this._loading[he];
          if (I)
            return I;
          try {
            return await (this._loading[he] = x(he));
          } finally {
            delete this._loading[he];
          }
        }
      }
      // Adds schema to the instance
      addSchema(A, D, x, v = this.opts.validateSchema) {
        if (Array.isArray(A)) {
          for (const U of A)
            this.addSchema(U, void 0, x, v);
          return this;
        }
        let R;
        if (typeof A == "object") {
          const { schemaId: U } = this.opts;
          if (R = A[U], R !== void 0 && typeof R != "string")
            throw new Error(`schema ${U} must be string`);
        }
        return D = (0, c.normalizeId)(D || R), this._checkUnique(D), this.schemas[D] = this._addSchema(A, x, D, v, !0), this;
      }
      // Add schema that will be used to validate other schemas
      // options in META_IGNORE_OPTIONS are alway set to false
      addMetaSchema(A, D, x = this.opts.validateSchema) {
        return this.addSchema(A, D, !0, x), this;
      }
      //  Validate schema against its meta-schema
      validateSchema(A, D) {
        if (typeof A == "boolean")
          return !0;
        let x;
        if (x = A.$schema, x !== void 0 && typeof x != "string")
          throw new Error("$schema must be a string");
        if (x = x || this.opts.defaultMeta || this.defaultMeta(), !x)
          return this.logger.warn("meta-schema not available"), this.errors = null, !0;
        const v = this.validate(x, A);
        if (!v && D) {
          const R = "schema is invalid: " + this.errorsText();
          if (this.opts.validateSchema === "log")
            this.logger.error(R);
          else
            throw new Error(R);
        }
        return v;
      }
      // Get compiled schema by `key` or `ref`.
      // (`key` that was passed to `addSchema` or full schema reference - `schema.$id` or resolved id)
      getSchema(A) {
        let D;
        for (; typeof (D = f.call(this, A)) == "string"; )
          A = D;
        if (D === void 0) {
          const { schemaId: x } = this.opts, v = new o.SchemaEnv({ schema: {}, schemaId: x });
          if (D = o.resolveSchema.call(this, v, A), !D)
            return;
          this.refs[A] = D;
        }
        return D.validate || this._compileSchemaEnv(D);
      }
      // Remove cached schema(s).
      // If no parameter is passed all schemas but meta-schemas are removed.
      // If RegExp is passed all schemas with key/id matching pattern but meta-schemas are removed.
      // Even if schema is referenced by other schemas it still can be removed as other schemas have local references.
      removeSchema(A) {
        if (A instanceof RegExp)
          return this._removeAllSchemas(this.schemas, A), this._removeAllSchemas(this.refs, A), this;
        switch (typeof A) {
          case "undefined":
            return this._removeAllSchemas(this.schemas), this._removeAllSchemas(this.refs), this._cache.clear(), this;
          case "string": {
            const D = f.call(this, A);
            return typeof D == "object" && this._cache.delete(D.schema), delete this.schemas[A], delete this.refs[A], this;
          }
          case "object": {
            const D = A;
            this._cache.delete(D);
            let x = A[this.opts.schemaId];
            return x && (x = (0, c.normalizeId)(x), delete this.schemas[x], delete this.refs[x]), this;
          }
          default:
            throw new Error("ajv.removeSchema: invalid parameter");
        }
      }
      // add "vocabulary" - a collection of keywords
      addVocabulary(A) {
        for (const D of A)
          this.addKeyword(D);
        return this;
      }
      addKeyword(A, D) {
        let x;
        if (typeof A == "string")
          x = A, typeof D == "object" && (this.logger.warn("these parameters are deprecated, see docs for addKeyword"), D.keyword = x);
        else if (typeof A == "object" && D === void 0) {
          if (D = A, x = D.keyword, Array.isArray(x) && !x.length)
            throw new Error("addKeywords: keyword must be string or non-empty array");
        } else
          throw new Error("invalid addKeywords parameters");
        if (q.call(this, x, D), !D)
          return (0, l.eachItem)(x, (R) => se.call(this, R)), this;
        be.call(this, D);
        const v = {
          ...D,
          type: (0, u.getJSONTypes)(D.type),
          schemaType: (0, u.getJSONTypes)(D.schemaType)
        };
        return (0, l.eachItem)(x, v.type.length === 0 ? (R) => se.call(this, R, v) : (R) => v.type.forEach((U) => se.call(this, R, v, U))), this;
      }
      getKeyword(A) {
        const D = this.RULES.all[A];
        return typeof D == "object" ? D.definition : !!D;
      }
      // Remove keyword
      removeKeyword(A) {
        const { RULES: D } = this;
        delete D.keywords[A], delete D.all[A];
        for (const x of D.rules) {
          const v = x.rules.findIndex((R) => R.keyword === A);
          v >= 0 && x.rules.splice(v, 1);
        }
        return this;
      }
      // Add format
      addFormat(A, D) {
        return typeof D == "string" && (D = new RegExp(D)), this.formats[A] = D, this;
      }
      errorsText(A = this.errors, { separator: D = ", ", dataVar: x = "data" } = {}) {
        return !A || A.length === 0 ? "No errors" : A.map((v) => `${x}${v.instancePath} ${v.message}`).reduce((v, R) => v + D + R);
      }
      $dataMetaSchema(A, D) {
        const x = this.RULES.all;
        A = JSON.parse(JSON.stringify(A));
        for (const v of D) {
          const R = v.split("/").slice(1);
          let U = A;
          for (const X of R)
            U = U[X];
          for (const X in x) {
            const ne = x[X];
            if (typeof ne != "object")
              continue;
            const { $data: ye } = ne.definition, he = U[X];
            ye && he && (U[X] = Me(he));
          }
        }
        return A;
      }
      _removeAllSchemas(A, D) {
        for (const x in A) {
          const v = A[x];
          (!D || D.test(x)) && (typeof v == "string" ? delete A[x] : v && !v.meta && (this._cache.delete(v.schema), delete A[x]));
        }
      }
      _addSchema(A, D, x, v = this.opts.validateSchema, R = this.opts.addUsedSchema) {
        let U;
        const { schemaId: X } = this.opts;
        if (typeof A == "object")
          U = A[X];
        else {
          if (this.opts.jtd)
            throw new Error("schema must be object");
          if (typeof A != "boolean")
            throw new Error("schema must be object or boolean");
        }
        let ne = this._cache.get(A);
        if (ne !== void 0)
          return ne;
        x = (0, c.normalizeId)(U || x);
        const ye = c.getSchemaRefs.call(this, A, x);
        return ne = new o.SchemaEnv({ schema: A, schemaId: X, meta: D, baseId: x, localRefs: ye }), this._cache.set(ne.schema, ne), R && !x.startsWith("#") && (x && this._checkUnique(x), this.refs[x] = ne), v && this.validateSchema(A, !0), ne;
      }
      _checkUnique(A) {
        if (this.schemas[A] || this.refs[A])
          throw new Error(`schema with key or id "${A}" already exists`);
      }
      _compileSchemaEnv(A) {
        if (A.meta ? this._compileMetaSchema(A) : o.compileSchema.call(this, A), !A.validate)
          throw new Error("ajv implementation error");
        return A.validate;
      }
      _compileMetaSchema(A) {
        const D = this.opts;
        this.opts = this._metaOpts;
        try {
          o.compileSchema.call(this, A);
        } finally {
          this.opts = D;
        }
      }
    }
    $.ValidationError = n.default, $.MissingRefError = s.default, t.default = $;
    function d(Z, A, D, x = "error") {
      for (const v in Z) {
        const R = v;
        R in A && this.logger[x](`${D}: option ${v}. ${Z[R]}`);
      }
    }
    function f(Z) {
      return Z = (0, c.normalizeId)(Z), this.schemas[Z] || this.refs[Z];
    }
    function _() {
      const Z = this.opts.schemas;
      if (Z)
        if (Array.isArray(Z))
          this.addSchema(Z);
        else
          for (const A in Z)
            this.addSchema(Z[A], A);
    }
    function E() {
      for (const Z in this.opts.formats) {
        const A = this.opts.formats[Z];
        A && this.addFormat(Z, A);
      }
    }
    function z(Z) {
      if (Array.isArray(Z)) {
        this.addVocabulary(Z);
        return;
      }
      this.logger.warn("keywords option as map is deprecated, pass array");
      for (const A in Z) {
        const D = Z[A];
        D.keyword || (D.keyword = A), this.addKeyword(D);
      }
    }
    function C() {
      const Z = { ...this.opts };
      for (const A of S)
        delete Z[A];
      return Z;
    }
    const P = { log() {
    }, warn() {
    }, error() {
    } };
    function j(Z) {
      if (Z === !1)
        return P;
      if (Z === void 0)
        return console;
      if (Z.log && Z.warn && Z.error)
        return Z;
      throw new Error("logger must implement log, warn and error methods");
    }
    const O = /^[a-z_$][a-z0-9_$:-]*$/i;
    function q(Z, A) {
      const { RULES: D } = this;
      if ((0, l.eachItem)(Z, (x) => {
        if (D.keywords[x])
          throw new Error(`Keyword ${x} is already defined`);
        if (!O.test(x))
          throw new Error(`Keyword ${x} has invalid name`);
      }), !!A && A.$data && !("code" in A || "validate" in A))
        throw new Error('$data keyword must have "code" or "validate" function');
    }
    function se(Z, A, D) {
      var x;
      const v = A?.post;
      if (D && v)
        throw new Error('keyword with "post" flag cannot have "type"');
      const { RULES: R } = this;
      let U = v ? R.post : R.rules.find(({ type: ne }) => ne === D);
      if (U || (U = { type: D, rules: [] }, R.rules.push(U)), R.keywords[Z] = !0, !A)
        return;
      const X = {
        keyword: Z,
        definition: {
          ...A,
          type: (0, u.getJSONTypes)(A.type),
          schemaType: (0, u.getJSONTypes)(A.schemaType)
        }
      };
      A.before ? Ee.call(this, U, X, A.before) : U.rules.push(X), R.all[Z] = X, (x = A.implements) === null || x === void 0 || x.forEach((ne) => this.addKeyword(ne));
    }
    function Ee(Z, A, D) {
      const x = Z.rules.findIndex((v) => v.keyword === D);
      x >= 0 ? Z.rules.splice(x, 0, A) : (Z.rules.push(A), this.logger.warn(`rule ${D} is not defined`));
    }
    function be(Z) {
      let { metaSchema: A } = Z;
      A !== void 0 && (Z.$data && this.opts.$data && (A = Me(A)), Z.validateSchema = this.compile(A, !0));
    }
    const oe = {
      $ref: "https://raw.githubusercontent.com/ajv-validator/ajv/master/lib/refs/data.json#"
    };
    function Me(Z) {
      return { anyOf: [Z, oe] };
    }
  })(Ei)), Ei;
}
var Nn = {}, xn = {}, zn = {}, pu;
function Xb() {
  if (pu) return zn;
  pu = 1, Object.defineProperty(zn, "__esModule", { value: !0 });
  const t = {
    keyword: "id",
    code() {
      throw new Error('NOT SUPPORTED: keyword "id", use "$id" for schema ID');
    }
  };
  return zn.default = t, zn;
}
var zt = {}, mu;
function e0() {
  if (mu) return zt;
  mu = 1, Object.defineProperty(zt, "__esModule", { value: !0 }), zt.callRef = zt.getValidate = void 0;
  const t = /* @__PURE__ */ oi(), e = /* @__PURE__ */ yt(), r = /* @__PURE__ */ ce(), n = /* @__PURE__ */ Vt(), s = /* @__PURE__ */ ya(), i = /* @__PURE__ */ fe(), o = {
    keyword: "$ref",
    schemaType: "string",
    code(u) {
      const { gen: l, schema: h, it: p } = u, { baseId: g, schemaEnv: S, validateName: k, opts: y, self: b } = p, { root: m } = S;
      if ((h === "#" || h === "#/") && g === m.baseId)
        return $();
      const w = s.resolveRef.call(b, m, g, h);
      if (w === void 0)
        throw new t.default(p.opts.uriResolver, g, h);
      if (w instanceof s.SchemaEnv)
        return d(w);
      return f(w);
      function $() {
        if (S === m)
          return c(u, k, S, S.$async);
        const _ = l.scopeValue("root", { ref: m });
        return c(u, (0, r._)`${_}.validate`, m, m.$async);
      }
      function d(_) {
        const E = a(u, _);
        c(u, E, _, _.$async);
      }
      function f(_) {
        const E = l.scopeValue("schema", y.code.source === !0 ? { ref: _, code: (0, r.stringify)(_) } : { ref: _ }), z = l.name("valid"), C = u.subschema({
          schema: _,
          dataTypes: [],
          schemaPath: r.nil,
          topSchemaRef: E,
          errSchemaPath: h
        }, z);
        u.mergeEvaluated(C), u.ok(z);
      }
    }
  };
  function a(u, l) {
    const { gen: h } = u;
    return l.validate ? h.scopeValue("validate", { ref: l.validate }) : (0, r._)`${h.scopeValue("wrapper", { ref: l })}.validate`;
  }
  zt.getValidate = a;
  function c(u, l, h, p) {
    const { gen: g, it: S } = u, { allErrors: k, schemaEnv: y, opts: b } = S, m = b.passContext ? n.default.this : r.nil;
    p ? w() : $();
    function w() {
      if (!y.$async)
        throw new Error("async schema referenced by sync schema");
      const _ = g.let("valid");
      g.try(() => {
        g.code((0, r._)`await ${(0, e.callValidateCode)(u, l, m)}`), f(l), k || g.assign(_, !0);
      }, (E) => {
        g.if((0, r._)`!(${E} instanceof ${S.ValidationError})`, () => g.throw(E)), d(E), k || g.assign(_, !1);
      }), u.ok(_);
    }
    function $() {
      u.result((0, e.callValidateCode)(u, l, m), () => f(l), () => d(l));
    }
    function d(_) {
      const E = (0, r._)`${_}.errors`;
      g.assign(n.default.vErrors, (0, r._)`${n.default.vErrors} === null ? ${E} : ${n.default.vErrors}.concat(${E})`), g.assign(n.default.errors, (0, r._)`${n.default.vErrors}.length`);
    }
    function f(_) {
      var E;
      if (!S.opts.unevaluated)
        return;
      const z = (E = h?.validate) === null || E === void 0 ? void 0 : E.evaluated;
      if (S.props !== !0)
        if (z && !z.dynamicProps)
          z.props !== void 0 && (S.props = i.mergeEvaluated.props(g, z.props, S.props));
        else {
          const C = g.var("props", (0, r._)`${_}.evaluated.props`);
          S.props = i.mergeEvaluated.props(g, C, S.props, r.Name);
        }
      if (S.items !== !0)
        if (z && !z.dynamicItems)
          z.items !== void 0 && (S.items = i.mergeEvaluated.items(g, z.items, S.items));
        else {
          const C = g.var("items", (0, r._)`${_}.evaluated.items`);
          S.items = i.mergeEvaluated.items(g, C, S.items, r.Name);
        }
    }
  }
  return zt.callRef = c, zt.default = o, zt;
}
var gu;
function t0() {
  if (gu) return xn;
  gu = 1, Object.defineProperty(xn, "__esModule", { value: !0 });
  const t = /* @__PURE__ */ Xb(), e = /* @__PURE__ */ e0(), r = [
    "$schema",
    "$id",
    "$defs",
    "$vocabulary",
    { keyword: "$comment" },
    "definitions",
    t.default,
    e.default
  ];
  return xn.default = r, xn;
}
var jn = {}, Mn = {}, _u;
function r0() {
  if (_u) return Mn;
  _u = 1, Object.defineProperty(Mn, "__esModule", { value: !0 });
  const t = /* @__PURE__ */ ce(), e = t.operators, r = {
    maximum: { okStr: "<=", ok: e.LTE, fail: e.GT },
    minimum: { okStr: ">=", ok: e.GTE, fail: e.LT },
    exclusiveMaximum: { okStr: "<", ok: e.LT, fail: e.GTE },
    exclusiveMinimum: { okStr: ">", ok: e.GT, fail: e.LTE }
  }, n = {
    message: ({ keyword: i, schemaCode: o }) => (0, t.str)`must be ${r[i].okStr} ${o}`,
    params: ({ keyword: i, schemaCode: o }) => (0, t._)`{comparison: ${r[i].okStr}, limit: ${o}}`
  }, s = {
    keyword: Object.keys(r),
    type: "number",
    schemaType: "number",
    $data: !0,
    error: n,
    code(i) {
      const { keyword: o, data: a, schemaCode: c } = i;
      i.fail$data((0, t._)`${a} ${r[o].fail} ${c} || isNaN(${a})`);
    }
  };
  return Mn.default = s, Mn;
}
var qn = {}, yu;
function n0() {
  if (yu) return qn;
  yu = 1, Object.defineProperty(qn, "__esModule", { value: !0 });
  const t = /* @__PURE__ */ ce(), r = {
    keyword: "multipleOf",
    type: "number",
    schemaType: "number",
    $data: !0,
    error: {
      message: ({ schemaCode: n }) => (0, t.str)`must be multiple of ${n}`,
      params: ({ schemaCode: n }) => (0, t._)`{multipleOf: ${n}}`
    },
    code(n) {
      const { gen: s, data: i, schemaCode: o, it: a } = n, c = a.opts.multipleOfPrecision, u = s.let("res"), l = c ? (0, t._)`Math.abs(Math.round(${u}) - ${u}) > 1e-${c}` : (0, t._)`${u} !== parseInt(${u})`;
      n.fail$data((0, t._)`(${o} === 0 || (${u} = ${i}/${o}, ${l}))`);
    }
  };
  return qn.default = r, qn;
}
var Un = {}, Dn = {}, wu;
function s0() {
  if (wu) return Dn;
  wu = 1, Object.defineProperty(Dn, "__esModule", { value: !0 });
  function t(e) {
    const r = e.length;
    let n = 0, s = 0, i;
    for (; s < r; )
      n++, i = e.charCodeAt(s++), i >= 55296 && i <= 56319 && s < r && (i = e.charCodeAt(s), (i & 64512) === 56320 && s++);
    return n;
  }
  return Dn.default = t, t.code = 'require("ajv/dist/runtime/ucs2length").default', Dn;
}
var vu;
function i0() {
  if (vu) return Un;
  vu = 1, Object.defineProperty(Un, "__esModule", { value: !0 });
  const t = /* @__PURE__ */ ce(), e = /* @__PURE__ */ fe(), r = /* @__PURE__ */ s0(), s = {
    keyword: ["maxLength", "minLength"],
    type: "string",
    schemaType: "number",
    $data: !0,
    error: {
      message({ keyword: i, schemaCode: o }) {
        const a = i === "maxLength" ? "more" : "fewer";
        return (0, t.str)`must NOT have ${a} than ${o} characters`;
      },
      params: ({ schemaCode: i }) => (0, t._)`{limit: ${i}}`
    },
    code(i) {
      const { keyword: o, data: a, schemaCode: c, it: u } = i, l = o === "maxLength" ? t.operators.GT : t.operators.LT, h = u.opts.unicode === !1 ? (0, t._)`${a}.length` : (0, t._)`${(0, e.useFunc)(i.gen, r.default)}(${a})`;
      i.fail$data((0, t._)`${h} ${l} ${c}`);
    }
  };
  return Un.default = s, Un;
}
var Ln = {}, bu;
function o0() {
  if (bu) return Ln;
  bu = 1, Object.defineProperty(Ln, "__esModule", { value: !0 });
  const t = /* @__PURE__ */ yt(), e = /* @__PURE__ */ fe(), r = /* @__PURE__ */ ce(), s = {
    keyword: "pattern",
    type: "string",
    schemaType: "string",
    $data: !0,
    error: {
      message: ({ schemaCode: i }) => (0, r.str)`must match pattern "${i}"`,
      params: ({ schemaCode: i }) => (0, r._)`{pattern: ${i}}`
    },
    code(i) {
      const { gen: o, data: a, $data: c, schema: u, schemaCode: l, it: h } = i, p = h.opts.unicodeRegExp ? "u" : "";
      if (c) {
        const { regExp: g } = h.opts.code, S = g.code === "new RegExp" ? (0, r._)`new RegExp` : (0, e.useFunc)(o, g), k = o.let("valid");
        o.try(() => o.assign(k, (0, r._)`${S}(${l}, ${p}).test(${a})`), () => o.assign(k, !1)), i.fail$data((0, r._)`!${k}`);
      } else {
        const g = (0, t.usePattern)(i, u);
        i.fail$data((0, r._)`!${g}.test(${a})`);
      }
    }
  };
  return Ln.default = s, Ln;
}
var Zn = {}, Su;
function a0() {
  if (Su) return Zn;
  Su = 1, Object.defineProperty(Zn, "__esModule", { value: !0 });
  const t = /* @__PURE__ */ ce(), r = {
    keyword: ["maxProperties", "minProperties"],
    type: "object",
    schemaType: "number",
    $data: !0,
    error: {
      message({ keyword: n, schemaCode: s }) {
        const i = n === "maxProperties" ? "more" : "fewer";
        return (0, t.str)`must NOT have ${i} than ${s} properties`;
      },
      params: ({ schemaCode: n }) => (0, t._)`{limit: ${n}}`
    },
    code(n) {
      const { keyword: s, data: i, schemaCode: o } = n, a = s === "maxProperties" ? t.operators.GT : t.operators.LT;
      n.fail$data((0, t._)`Object.keys(${i}).length ${a} ${o}`);
    }
  };
  return Zn.default = r, Zn;
}
var Hn = {}, ku;
function c0() {
  if (ku) return Hn;
  ku = 1, Object.defineProperty(Hn, "__esModule", { value: !0 });
  const t = /* @__PURE__ */ yt(), e = /* @__PURE__ */ ce(), r = /* @__PURE__ */ fe(), s = {
    keyword: "required",
    type: "object",
    schemaType: "array",
    $data: !0,
    error: {
      message: ({ params: { missingProperty: i } }) => (0, e.str)`must have required property '${i}'`,
      params: ({ params: { missingProperty: i } }) => (0, e._)`{missingProperty: ${i}}`
    },
    code(i) {
      const { gen: o, schema: a, schemaCode: c, data: u, $data: l, it: h } = i, { opts: p } = h;
      if (!l && a.length === 0)
        return;
      const g = a.length >= p.loopRequired;
      if (h.allErrors ? S() : k(), p.strictRequired) {
        const m = i.parentSchema.properties, { definedProperties: w } = i.it;
        for (const $ of a)
          if (m?.[$] === void 0 && !w.has($)) {
            const d = h.schemaEnv.baseId + h.errSchemaPath, f = `required property "${$}" is not defined at "${d}" (strictRequired)`;
            (0, r.checkStrictMode)(h, f, h.opts.strictRequired);
          }
      }
      function S() {
        if (g || l)
          i.block$data(e.nil, y);
        else
          for (const m of a)
            (0, t.checkReportMissingProp)(i, m);
      }
      function k() {
        const m = o.let("missing");
        if (g || l) {
          const w = o.let("valid", !0);
          i.block$data(w, () => b(m, w)), i.ok(w);
        } else
          o.if((0, t.checkMissingProp)(i, a, m)), (0, t.reportMissingProp)(i, m), o.else();
      }
      function y() {
        o.forOf("prop", c, (m) => {
          i.setParams({ missingProperty: m }), o.if((0, t.noPropertyInData)(o, u, m, p.ownProperties), () => i.error());
        });
      }
      function b(m, w) {
        i.setParams({ missingProperty: m }), o.forOf(m, c, () => {
          o.assign(w, (0, t.propertyInData)(o, u, m, p.ownProperties)), o.if((0, e.not)(w), () => {
            i.error(), o.break();
          });
        }, e.nil);
      }
    }
  };
  return Hn.default = s, Hn;
}
var Fn = {}, $u;
function u0() {
  if ($u) return Fn;
  $u = 1, Object.defineProperty(Fn, "__esModule", { value: !0 });
  const t = /* @__PURE__ */ ce(), r = {
    keyword: ["maxItems", "minItems"],
    type: "array",
    schemaType: "number",
    $data: !0,
    error: {
      message({ keyword: n, schemaCode: s }) {
        const i = n === "maxItems" ? "more" : "fewer";
        return (0, t.str)`must NOT have ${i} than ${s} items`;
      },
      params: ({ schemaCode: n }) => (0, t._)`{limit: ${n}}`
    },
    code(n) {
      const { keyword: s, data: i, schemaCode: o } = n, a = s === "maxItems" ? t.operators.GT : t.operators.LT;
      n.fail$data((0, t._)`${i}.length ${a} ${o}`);
    }
  };
  return Fn.default = r, Fn;
}
var Vn = {}, Bn = {}, Eu;
function wa() {
  if (Eu) return Bn;
  Eu = 1, Object.defineProperty(Bn, "__esModule", { value: !0 });
  const t = Mh();
  return t.code = 'require("ajv/dist/runtime/equal").default', Bn.default = t, Bn;
}
var Tu;
function l0() {
  if (Tu) return Vn;
  Tu = 1, Object.defineProperty(Vn, "__esModule", { value: !0 });
  const t = /* @__PURE__ */ Us(), e = /* @__PURE__ */ ce(), r = /* @__PURE__ */ fe(), n = /* @__PURE__ */ wa(), i = {
    keyword: "uniqueItems",
    type: "array",
    schemaType: "boolean",
    $data: !0,
    error: {
      message: ({ params: { i: o, j: a } }) => (0, e.str)`must NOT have duplicate items (items ## ${a} and ${o} are identical)`,
      params: ({ params: { i: o, j: a } }) => (0, e._)`{i: ${o}, j: ${a}}`
    },
    code(o) {
      const { gen: a, data: c, $data: u, schema: l, parentSchema: h, schemaCode: p, it: g } = o;
      if (!u && !l)
        return;
      const S = a.let("valid"), k = h.items ? (0, t.getSchemaTypes)(h.items) : [];
      o.block$data(S, y, (0, e._)`${p} === false`), o.ok(S);
      function y() {
        const $ = a.let("i", (0, e._)`${c}.length`), d = a.let("j");
        o.setParams({ i: $, j: d }), a.assign(S, !0), a.if((0, e._)`${$} > 1`, () => (b() ? m : w)($, d));
      }
      function b() {
        return k.length > 0 && !k.some(($) => $ === "object" || $ === "array");
      }
      function m($, d) {
        const f = a.name("item"), _ = (0, t.checkDataTypes)(k, f, g.opts.strictNumbers, t.DataType.Wrong), E = a.const("indices", (0, e._)`{}`);
        a.for((0, e._)`;${$}--;`, () => {
          a.let(f, (0, e._)`${c}[${$}]`), a.if(_, (0, e._)`continue`), k.length > 1 && a.if((0, e._)`typeof ${f} == "string"`, (0, e._)`${f} += "_"`), a.if((0, e._)`typeof ${E}[${f}] == "number"`, () => {
            a.assign(d, (0, e._)`${E}[${f}]`), o.error(), a.assign(S, !1).break();
          }).code((0, e._)`${E}[${f}] = ${$}`);
        });
      }
      function w($, d) {
        const f = (0, r.useFunc)(a, n.default), _ = a.name("outer");
        a.label(_).for((0, e._)`;${$}--;`, () => a.for((0, e._)`${d} = ${$}; ${d}--;`, () => a.if((0, e._)`${f}(${c}[${$}], ${c}[${d}])`, () => {
          o.error(), a.assign(S, !1).break(_);
        })));
      }
    }
  };
  return Vn.default = i, Vn;
}
var Wn = {}, Ru;
function d0() {
  if (Ru) return Wn;
  Ru = 1, Object.defineProperty(Wn, "__esModule", { value: !0 });
  const t = /* @__PURE__ */ ce(), e = /* @__PURE__ */ fe(), r = /* @__PURE__ */ wa(), s = {
    keyword: "const",
    $data: !0,
    error: {
      message: "must be equal to constant",
      params: ({ schemaCode: i }) => (0, t._)`{allowedValue: ${i}}`
    },
    code(i) {
      const { gen: o, data: a, $data: c, schemaCode: u, schema: l } = i;
      c || l && typeof l == "object" ? i.fail$data((0, t._)`!${(0, e.useFunc)(o, r.default)}(${a}, ${u})`) : i.fail((0, t._)`${l} !== ${a}`);
    }
  };
  return Wn.default = s, Wn;
}
var Gn = {}, Iu;
function h0() {
  if (Iu) return Gn;
  Iu = 1, Object.defineProperty(Gn, "__esModule", { value: !0 });
  const t = /* @__PURE__ */ ce(), e = /* @__PURE__ */ fe(), r = /* @__PURE__ */ wa(), s = {
    keyword: "enum",
    schemaType: "array",
    $data: !0,
    error: {
      message: "must be equal to one of the allowed values",
      params: ({ schemaCode: i }) => (0, t._)`{allowedValues: ${i}}`
    },
    code(i) {
      const { gen: o, data: a, $data: c, schema: u, schemaCode: l, it: h } = i;
      if (!c && u.length === 0)
        throw new Error("enum must have non-empty array");
      const p = u.length >= h.opts.loopEnum;
      let g;
      const S = () => g ?? (g = (0, e.useFunc)(o, r.default));
      let k;
      if (p || c)
        k = o.let("valid"), i.block$data(k, y);
      else {
        if (!Array.isArray(u))
          throw new Error("ajv implementation error");
        const m = o.const("vSchema", l);
        k = (0, t.or)(...u.map((w, $) => b(m, $)));
      }
      i.pass(k);
      function y() {
        o.assign(k, !1), o.forOf("v", l, (m) => o.if((0, t._)`${S()}(${a}, ${m})`, () => o.assign(k, !0).break()));
      }
      function b(m, w) {
        const $ = u[w];
        return typeof $ == "object" && $ !== null ? (0, t._)`${S()}(${a}, ${m}[${w}])` : (0, t._)`${a} === ${$}`;
      }
    }
  };
  return Gn.default = s, Gn;
}
var Pu;
function f0() {
  if (Pu) return jn;
  Pu = 1, Object.defineProperty(jn, "__esModule", { value: !0 });
  const t = /* @__PURE__ */ r0(), e = /* @__PURE__ */ n0(), r = /* @__PURE__ */ i0(), n = /* @__PURE__ */ o0(), s = /* @__PURE__ */ a0(), i = /* @__PURE__ */ c0(), o = /* @__PURE__ */ u0(), a = /* @__PURE__ */ l0(), c = /* @__PURE__ */ d0(), u = /* @__PURE__ */ h0(), l = [
    // number
    t.default,
    e.default,
    // string
    r.default,
    n.default,
    // object
    s.default,
    i.default,
    // array
    o.default,
    a.default,
    // any
    { keyword: "type", schemaType: ["string", "array"] },
    { keyword: "nullable", schemaType: "boolean" },
    c.default,
    u.default
  ];
  return jn.default = l, jn;
}
var Jn = {}, cr = {}, Cu;
function Uh() {
  if (Cu) return cr;
  Cu = 1, Object.defineProperty(cr, "__esModule", { value: !0 }), cr.validateAdditionalItems = void 0;
  const t = /* @__PURE__ */ ce(), e = /* @__PURE__ */ fe(), n = {
    keyword: "additionalItems",
    type: "array",
    schemaType: ["boolean", "object"],
    before: "uniqueItems",
    error: {
      message: ({ params: { len: i } }) => (0, t.str)`must NOT have more than ${i} items`,
      params: ({ params: { len: i } }) => (0, t._)`{limit: ${i}}`
    },
    code(i) {
      const { parentSchema: o, it: a } = i, { items: c } = o;
      if (!Array.isArray(c)) {
        (0, e.checkStrictMode)(a, '"additionalItems" is ignored when "items" is not an array of schemas');
        return;
      }
      s(i, c);
    }
  };
  function s(i, o) {
    const { gen: a, schema: c, data: u, keyword: l, it: h } = i;
    h.items = !0;
    const p = a.const("len", (0, t._)`${u}.length`);
    if (c === !1)
      i.setParams({ len: o.length }), i.pass((0, t._)`${p} <= ${o.length}`);
    else if (typeof c == "object" && !(0, e.alwaysValidSchema)(h, c)) {
      const S = a.var("valid", (0, t._)`${p} <= ${o.length}`);
      a.if((0, t.not)(S), () => g(S)), i.ok(S);
    }
    function g(S) {
      a.forRange("i", o.length, p, (k) => {
        i.subschema({ keyword: l, dataProp: k, dataPropType: e.Type.Num }, S), h.allErrors || a.if((0, t.not)(S), () => a.break());
      });
    }
  }
  return cr.validateAdditionalItems = s, cr.default = n, cr;
}
var Kn = {}, ur = {}, Ou;
function Dh() {
  if (Ou) return ur;
  Ou = 1, Object.defineProperty(ur, "__esModule", { value: !0 }), ur.validateTuple = void 0;
  const t = /* @__PURE__ */ ce(), e = /* @__PURE__ */ fe(), r = /* @__PURE__ */ yt(), n = {
    keyword: "items",
    type: "array",
    schemaType: ["object", "array", "boolean"],
    before: "uniqueItems",
    code(i) {
      const { schema: o, it: a } = i;
      if (Array.isArray(o))
        return s(i, "additionalItems", o);
      a.items = !0, !(0, e.alwaysValidSchema)(a, o) && i.ok((0, r.validateArray)(i));
    }
  };
  function s(i, o, a = i.schema) {
    const { gen: c, parentSchema: u, data: l, keyword: h, it: p } = i;
    k(u), p.opts.unevaluated && a.length && p.items !== !0 && (p.items = e.mergeEvaluated.items(c, a.length, p.items));
    const g = c.name("valid"), S = c.const("len", (0, t._)`${l}.length`);
    a.forEach((y, b) => {
      (0, e.alwaysValidSchema)(p, y) || (c.if((0, t._)`${S} > ${b}`, () => i.subschema({
        keyword: h,
        schemaProp: b,
        dataProp: b
      }, g)), i.ok(g));
    });
    function k(y) {
      const { opts: b, errSchemaPath: m } = p, w = a.length, $ = w === y.minItems && (w === y.maxItems || y[o] === !1);
      if (b.strictTuples && !$) {
        const d = `"${h}" is ${w}-tuple, but minItems or maxItems/${o} are not specified or different at path "${m}"`;
        (0, e.checkStrictMode)(p, d, b.strictTuples);
      }
    }
  }
  return ur.validateTuple = s, ur.default = n, ur;
}
var Au;
function p0() {
  if (Au) return Kn;
  Au = 1, Object.defineProperty(Kn, "__esModule", { value: !0 });
  const t = /* @__PURE__ */ Dh(), e = {
    keyword: "prefixItems",
    type: "array",
    schemaType: ["array"],
    before: "uniqueItems",
    code: (r) => (0, t.validateTuple)(r, "items")
  };
  return Kn.default = e, Kn;
}
var Qn = {}, Nu;
function m0() {
  if (Nu) return Qn;
  Nu = 1, Object.defineProperty(Qn, "__esModule", { value: !0 });
  const t = /* @__PURE__ */ ce(), e = /* @__PURE__ */ fe(), r = /* @__PURE__ */ yt(), n = /* @__PURE__ */ Uh(), i = {
    keyword: "items",
    type: "array",
    schemaType: ["object", "boolean"],
    before: "uniqueItems",
    error: {
      message: ({ params: { len: o } }) => (0, t.str)`must NOT have more than ${o} items`,
      params: ({ params: { len: o } }) => (0, t._)`{limit: ${o}}`
    },
    code(o) {
      const { schema: a, parentSchema: c, it: u } = o, { prefixItems: l } = c;
      u.items = !0, !(0, e.alwaysValidSchema)(u, a) && (l ? (0, n.validateAdditionalItems)(o, l) : o.ok((0, r.validateArray)(o)));
    }
  };
  return Qn.default = i, Qn;
}
var Yn = {}, xu;
function g0() {
  if (xu) return Yn;
  xu = 1, Object.defineProperty(Yn, "__esModule", { value: !0 });
  const t = /* @__PURE__ */ ce(), e = /* @__PURE__ */ fe(), n = {
    keyword: "contains",
    type: "array",
    schemaType: ["object", "boolean"],
    before: "uniqueItems",
    trackErrors: !0,
    error: {
      message: ({ params: { min: s, max: i } }) => i === void 0 ? (0, t.str)`must contain at least ${s} valid item(s)` : (0, t.str)`must contain at least ${s} and no more than ${i} valid item(s)`,
      params: ({ params: { min: s, max: i } }) => i === void 0 ? (0, t._)`{minContains: ${s}}` : (0, t._)`{minContains: ${s}, maxContains: ${i}}`
    },
    code(s) {
      const { gen: i, schema: o, parentSchema: a, data: c, it: u } = s;
      let l, h;
      const { minContains: p, maxContains: g } = a;
      u.opts.next ? (l = p === void 0 ? 1 : p, h = g) : l = 1;
      const S = i.const("len", (0, t._)`${c}.length`);
      if (s.setParams({ min: l, max: h }), h === void 0 && l === 0) {
        (0, e.checkStrictMode)(u, '"minContains" == 0 without "maxContains": "contains" keyword ignored');
        return;
      }
      if (h !== void 0 && l > h) {
        (0, e.checkStrictMode)(u, '"minContains" > "maxContains" is always invalid'), s.fail();
        return;
      }
      if ((0, e.alwaysValidSchema)(u, o)) {
        let w = (0, t._)`${S} >= ${l}`;
        h !== void 0 && (w = (0, t._)`${w} && ${S} <= ${h}`), s.pass(w);
        return;
      }
      u.items = !0;
      const k = i.name("valid");
      h === void 0 && l === 1 ? b(k, () => i.if(k, () => i.break())) : l === 0 ? (i.let(k, !0), h !== void 0 && i.if((0, t._)`${c}.length > 0`, y)) : (i.let(k, !1), y()), s.result(k, () => s.reset());
      function y() {
        const w = i.name("_valid"), $ = i.let("count", 0);
        b(w, () => i.if(w, () => m($)));
      }
      function b(w, $) {
        i.forRange("i", 0, S, (d) => {
          s.subschema({
            keyword: "contains",
            dataProp: d,
            dataPropType: e.Type.Num,
            compositeRule: !0
          }, w), $();
        });
      }
      function m(w) {
        i.code((0, t._)`${w}++`), h === void 0 ? i.if((0, t._)`${w} >= ${l}`, () => i.assign(k, !0).break()) : (i.if((0, t._)`${w} > ${h}`, () => i.assign(k, !1).break()), l === 1 ? i.assign(k, !0) : i.if((0, t._)`${w} >= ${l}`, () => i.assign(k, !0)));
      }
    }
  };
  return Yn.default = n, Yn;
}
var xi = {}, zu;
function _0() {
  return zu || (zu = 1, (function(t) {
    Object.defineProperty(t, "__esModule", { value: !0 }), t.validateSchemaDeps = t.validatePropertyDeps = t.error = void 0;
    const e = /* @__PURE__ */ ce(), r = /* @__PURE__ */ fe(), n = /* @__PURE__ */ yt();
    t.error = {
      message: ({ params: { property: c, depsCount: u, deps: l } }) => {
        const h = u === 1 ? "property" : "properties";
        return (0, e.str)`must have ${h} ${l} when property ${c} is present`;
      },
      params: ({ params: { property: c, depsCount: u, deps: l, missingProperty: h } }) => (0, e._)`{property: ${c},
    missingProperty: ${h},
    depsCount: ${u},
    deps: ${l}}`
      // TODO change to reference
    };
    const s = {
      keyword: "dependencies",
      type: "object",
      schemaType: "object",
      error: t.error,
      code(c) {
        const [u, l] = i(c);
        o(c, u), a(c, l);
      }
    };
    function i({ schema: c }) {
      const u = {}, l = {};
      for (const h in c) {
        if (h === "__proto__")
          continue;
        const p = Array.isArray(c[h]) ? u : l;
        p[h] = c[h];
      }
      return [u, l];
    }
    function o(c, u = c.schema) {
      const { gen: l, data: h, it: p } = c;
      if (Object.keys(u).length === 0)
        return;
      const g = l.let("missing");
      for (const S in u) {
        const k = u[S];
        if (k.length === 0)
          continue;
        const y = (0, n.propertyInData)(l, h, S, p.opts.ownProperties);
        c.setParams({
          property: S,
          depsCount: k.length,
          deps: k.join(", ")
        }), p.allErrors ? l.if(y, () => {
          for (const b of k)
            (0, n.checkReportMissingProp)(c, b);
        }) : (l.if((0, e._)`${y} && (${(0, n.checkMissingProp)(c, k, g)})`), (0, n.reportMissingProp)(c, g), l.else());
      }
    }
    t.validatePropertyDeps = o;
    function a(c, u = c.schema) {
      const { gen: l, data: h, keyword: p, it: g } = c, S = l.name("valid");
      for (const k in u)
        (0, r.alwaysValidSchema)(g, u[k]) || (l.if(
          (0, n.propertyInData)(l, h, k, g.opts.ownProperties),
          () => {
            const y = c.subschema({ keyword: p, schemaProp: k }, S);
            c.mergeValidEvaluated(y, S);
          },
          () => l.var(S, !0)
          // TODO var
        ), c.ok(S));
    }
    t.validateSchemaDeps = a, t.default = s;
  })(xi)), xi;
}
var Xn = {}, ju;
function y0() {
  if (ju) return Xn;
  ju = 1, Object.defineProperty(Xn, "__esModule", { value: !0 });
  const t = /* @__PURE__ */ ce(), e = /* @__PURE__ */ fe(), n = {
    keyword: "propertyNames",
    type: "object",
    schemaType: ["object", "boolean"],
    error: {
      message: "property name must be valid",
      params: ({ params: s }) => (0, t._)`{propertyName: ${s.propertyName}}`
    },
    code(s) {
      const { gen: i, schema: o, data: a, it: c } = s;
      if ((0, e.alwaysValidSchema)(c, o))
        return;
      const u = i.name("valid");
      i.forIn("key", a, (l) => {
        s.setParams({ propertyName: l }), s.subschema({
          keyword: "propertyNames",
          data: l,
          dataTypes: ["string"],
          propertyName: l,
          compositeRule: !0
        }, u), i.if((0, t.not)(u), () => {
          s.error(!0), c.allErrors || i.break();
        });
      }), s.ok(u);
    }
  };
  return Xn.default = n, Xn;
}
var es = {}, Mu;
function Lh() {
  if (Mu) return es;
  Mu = 1, Object.defineProperty(es, "__esModule", { value: !0 });
  const t = /* @__PURE__ */ yt(), e = /* @__PURE__ */ ce(), r = /* @__PURE__ */ Vt(), n = /* @__PURE__ */ fe(), i = {
    keyword: "additionalProperties",
    type: ["object"],
    schemaType: ["boolean", "object"],
    allowUndefined: !0,
    trackErrors: !0,
    error: {
      message: "must NOT have additional properties",
      params: ({ params: o }) => (0, e._)`{additionalProperty: ${o.additionalProperty}}`
    },
    code(o) {
      const { gen: a, schema: c, parentSchema: u, data: l, errsCount: h, it: p } = o;
      if (!h)
        throw new Error("ajv implementation error");
      const { allErrors: g, opts: S } = p;
      if (p.props = !0, S.removeAdditional !== "all" && (0, n.alwaysValidSchema)(p, c))
        return;
      const k = (0, t.allSchemaProperties)(u.properties), y = (0, t.allSchemaProperties)(u.patternProperties);
      b(), o.ok((0, e._)`${h} === ${r.default.errors}`);
      function b() {
        a.forIn("key", l, (f) => {
          !k.length && !y.length ? $(f) : a.if(m(f), () => $(f));
        });
      }
      function m(f) {
        let _;
        if (k.length > 8) {
          const E = (0, n.schemaRefOrVal)(p, u.properties, "properties");
          _ = (0, t.isOwnProperty)(a, E, f);
        } else k.length ? _ = (0, e.or)(...k.map((E) => (0, e._)`${f} === ${E}`)) : _ = e.nil;
        return y.length && (_ = (0, e.or)(_, ...y.map((E) => (0, e._)`${(0, t.usePattern)(o, E)}.test(${f})`))), (0, e.not)(_);
      }
      function w(f) {
        a.code((0, e._)`delete ${l}[${f}]`);
      }
      function $(f) {
        if (S.removeAdditional === "all" || S.removeAdditional && c === !1) {
          w(f);
          return;
        }
        if (c === !1) {
          o.setParams({ additionalProperty: f }), o.error(), g || a.break();
          return;
        }
        if (typeof c == "object" && !(0, n.alwaysValidSchema)(p, c)) {
          const _ = a.name("valid");
          S.removeAdditional === "failing" ? (d(f, _, !1), a.if((0, e.not)(_), () => {
            o.reset(), w(f);
          })) : (d(f, _), g || a.if((0, e.not)(_), () => a.break()));
        }
      }
      function d(f, _, E) {
        const z = {
          keyword: "additionalProperties",
          dataProp: f,
          dataPropType: n.Type.Str
        };
        E === !1 && Object.assign(z, {
          compositeRule: !0,
          createErrors: !1,
          allErrors: !1
        }), o.subschema(z, _);
      }
    }
  };
  return es.default = i, es;
}
var ts = {}, qu;
function w0() {
  if (qu) return ts;
  qu = 1, Object.defineProperty(ts, "__esModule", { value: !0 });
  const t = /* @__PURE__ */ ii(), e = /* @__PURE__ */ yt(), r = /* @__PURE__ */ fe(), n = /* @__PURE__ */ Lh(), s = {
    keyword: "properties",
    type: "object",
    schemaType: "object",
    code(i) {
      const { gen: o, schema: a, parentSchema: c, data: u, it: l } = i;
      l.opts.removeAdditional === "all" && c.additionalProperties === void 0 && n.default.code(new t.KeywordCxt(l, n.default, "additionalProperties"));
      const h = (0, e.allSchemaProperties)(a);
      for (const y of h)
        l.definedProperties.add(y);
      l.opts.unevaluated && h.length && l.props !== !0 && (l.props = r.mergeEvaluated.props(o, (0, r.toHash)(h), l.props));
      const p = h.filter((y) => !(0, r.alwaysValidSchema)(l, a[y]));
      if (p.length === 0)
        return;
      const g = o.name("valid");
      for (const y of p)
        S(y) ? k(y) : (o.if((0, e.propertyInData)(o, u, y, l.opts.ownProperties)), k(y), l.allErrors || o.else().var(g, !0), o.endIf()), i.it.definedProperties.add(y), i.ok(g);
      function S(y) {
        return l.opts.useDefaults && !l.compositeRule && a[y].default !== void 0;
      }
      function k(y) {
        i.subschema({
          keyword: "properties",
          schemaProp: y,
          dataProp: y
        }, g);
      }
    }
  };
  return ts.default = s, ts;
}
var rs = {}, Uu;
function v0() {
  if (Uu) return rs;
  Uu = 1, Object.defineProperty(rs, "__esModule", { value: !0 });
  const t = /* @__PURE__ */ yt(), e = /* @__PURE__ */ ce(), r = /* @__PURE__ */ fe(), n = /* @__PURE__ */ fe(), s = {
    keyword: "patternProperties",
    type: "object",
    schemaType: "object",
    code(i) {
      const { gen: o, schema: a, data: c, parentSchema: u, it: l } = i, { opts: h } = l, p = (0, t.allSchemaProperties)(a), g = p.filter(($) => (0, r.alwaysValidSchema)(l, a[$]));
      if (p.length === 0 || g.length === p.length && (!l.opts.unevaluated || l.props === !0))
        return;
      const S = h.strictSchema && !h.allowMatchingProperties && u.properties, k = o.name("valid");
      l.props !== !0 && !(l.props instanceof e.Name) && (l.props = (0, n.evaluatedPropsToName)(o, l.props));
      const { props: y } = l;
      b();
      function b() {
        for (const $ of p)
          S && m($), l.allErrors ? w($) : (o.var(k, !0), w($), o.if(k));
      }
      function m($) {
        for (const d in S)
          new RegExp($).test(d) && (0, r.checkStrictMode)(l, `property ${d} matches pattern ${$} (use allowMatchingProperties)`);
      }
      function w($) {
        o.forIn("key", c, (d) => {
          o.if((0, e._)`${(0, t.usePattern)(i, $)}.test(${d})`, () => {
            const f = g.includes($);
            f || i.subschema({
              keyword: "patternProperties",
              schemaProp: $,
              dataProp: d,
              dataPropType: n.Type.Str
            }, k), l.opts.unevaluated && y !== !0 ? o.assign((0, e._)`${y}[${d}]`, !0) : !f && !l.allErrors && o.if((0, e.not)(k), () => o.break());
          });
        });
      }
    }
  };
  return rs.default = s, rs;
}
var ns = {}, Du;
function b0() {
  if (Du) return ns;
  Du = 1, Object.defineProperty(ns, "__esModule", { value: !0 });
  const t = /* @__PURE__ */ fe(), e = {
    keyword: "not",
    schemaType: ["object", "boolean"],
    trackErrors: !0,
    code(r) {
      const { gen: n, schema: s, it: i } = r;
      if ((0, t.alwaysValidSchema)(i, s)) {
        r.fail();
        return;
      }
      const o = n.name("valid");
      r.subschema({
        keyword: "not",
        compositeRule: !0,
        createErrors: !1,
        allErrors: !1
      }, o), r.failResult(o, () => r.reset(), () => r.error());
    },
    error: { message: "must NOT be valid" }
  };
  return ns.default = e, ns;
}
var ss = {}, Lu;
function S0() {
  if (Lu) return ss;
  Lu = 1, Object.defineProperty(ss, "__esModule", { value: !0 });
  const e = {
    keyword: "anyOf",
    schemaType: "array",
    trackErrors: !0,
    code: (/* @__PURE__ */ yt()).validateUnion,
    error: { message: "must match a schema in anyOf" }
  };
  return ss.default = e, ss;
}
var is = {}, Zu;
function k0() {
  if (Zu) return is;
  Zu = 1, Object.defineProperty(is, "__esModule", { value: !0 });
  const t = /* @__PURE__ */ ce(), e = /* @__PURE__ */ fe(), n = {
    keyword: "oneOf",
    schemaType: "array",
    trackErrors: !0,
    error: {
      message: "must match exactly one schema in oneOf",
      params: ({ params: s }) => (0, t._)`{passingSchemas: ${s.passing}}`
    },
    code(s) {
      const { gen: i, schema: o, parentSchema: a, it: c } = s;
      if (!Array.isArray(o))
        throw new Error("ajv implementation error");
      if (c.opts.discriminator && a.discriminator)
        return;
      const u = o, l = i.let("valid", !1), h = i.let("passing", null), p = i.name("_valid");
      s.setParams({ passing: h }), i.block(g), s.result(l, () => s.reset(), () => s.error(!0));
      function g() {
        u.forEach((S, k) => {
          let y;
          (0, e.alwaysValidSchema)(c, S) ? i.var(p, !0) : y = s.subschema({
            keyword: "oneOf",
            schemaProp: k,
            compositeRule: !0
          }, p), k > 0 && i.if((0, t._)`${p} && ${l}`).assign(l, !1).assign(h, (0, t._)`[${h}, ${k}]`).else(), i.if(p, () => {
            i.assign(l, !0), i.assign(h, k), y && s.mergeEvaluated(y, t.Name);
          });
        });
      }
    }
  };
  return is.default = n, is;
}
var os = {}, Hu;
function $0() {
  if (Hu) return os;
  Hu = 1, Object.defineProperty(os, "__esModule", { value: !0 });
  const t = /* @__PURE__ */ fe(), e = {
    keyword: "allOf",
    schemaType: "array",
    code(r) {
      const { gen: n, schema: s, it: i } = r;
      if (!Array.isArray(s))
        throw new Error("ajv implementation error");
      const o = n.name("valid");
      s.forEach((a, c) => {
        if ((0, t.alwaysValidSchema)(i, a))
          return;
        const u = r.subschema({ keyword: "allOf", schemaProp: c }, o);
        r.ok(o), r.mergeEvaluated(u);
      });
    }
  };
  return os.default = e, os;
}
var as = {}, Fu;
function E0() {
  if (Fu) return as;
  Fu = 1, Object.defineProperty(as, "__esModule", { value: !0 });
  const t = /* @__PURE__ */ ce(), e = /* @__PURE__ */ fe(), n = {
    keyword: "if",
    schemaType: ["object", "boolean"],
    trackErrors: !0,
    error: {
      message: ({ params: i }) => (0, t.str)`must match "${i.ifClause}" schema`,
      params: ({ params: i }) => (0, t._)`{failingKeyword: ${i.ifClause}}`
    },
    code(i) {
      const { gen: o, parentSchema: a, it: c } = i;
      a.then === void 0 && a.else === void 0 && (0, e.checkStrictMode)(c, '"if" without "then" and "else" is ignored');
      const u = s(c, "then"), l = s(c, "else");
      if (!u && !l)
        return;
      const h = o.let("valid", !0), p = o.name("_valid");
      if (g(), i.reset(), u && l) {
        const k = o.let("ifClause");
        i.setParams({ ifClause: k }), o.if(p, S("then", k), S("else", k));
      } else u ? o.if(p, S("then")) : o.if((0, t.not)(p), S("else"));
      i.pass(h, () => i.error(!0));
      function g() {
        const k = i.subschema({
          keyword: "if",
          compositeRule: !0,
          createErrors: !1,
          allErrors: !1
        }, p);
        i.mergeEvaluated(k);
      }
      function S(k, y) {
        return () => {
          const b = i.subschema({ keyword: k }, p);
          o.assign(h, p), i.mergeValidEvaluated(b, h), y ? o.assign(y, (0, t._)`${k}`) : i.setParams({ ifClause: k });
        };
      }
    }
  };
  function s(i, o) {
    const a = i.schema[o];
    return a !== void 0 && !(0, e.alwaysValidSchema)(i, a);
  }
  return as.default = n, as;
}
var cs = {}, Vu;
function T0() {
  if (Vu) return cs;
  Vu = 1, Object.defineProperty(cs, "__esModule", { value: !0 });
  const t = /* @__PURE__ */ fe(), e = {
    keyword: ["then", "else"],
    schemaType: ["object", "boolean"],
    code({ keyword: r, parentSchema: n, it: s }) {
      n.if === void 0 && (0, t.checkStrictMode)(s, `"${r}" without "if" is ignored`);
    }
  };
  return cs.default = e, cs;
}
var Bu;
function R0() {
  if (Bu) return Jn;
  Bu = 1, Object.defineProperty(Jn, "__esModule", { value: !0 });
  const t = /* @__PURE__ */ Uh(), e = /* @__PURE__ */ p0(), r = /* @__PURE__ */ Dh(), n = /* @__PURE__ */ m0(), s = /* @__PURE__ */ g0(), i = /* @__PURE__ */ _0(), o = /* @__PURE__ */ y0(), a = /* @__PURE__ */ Lh(), c = /* @__PURE__ */ w0(), u = /* @__PURE__ */ v0(), l = /* @__PURE__ */ b0(), h = /* @__PURE__ */ S0(), p = /* @__PURE__ */ k0(), g = /* @__PURE__ */ $0(), S = /* @__PURE__ */ E0(), k = /* @__PURE__ */ T0();
  function y(b = !1) {
    const m = [
      // any
      l.default,
      h.default,
      p.default,
      g.default,
      S.default,
      k.default,
      // object
      o.default,
      a.default,
      i.default,
      c.default,
      u.default
    ];
    return b ? m.push(e.default, n.default) : m.push(t.default, r.default), m.push(s.default), m;
  }
  return Jn.default = y, Jn;
}
var us = {}, ls = {}, Wu;
function I0() {
  if (Wu) return ls;
  Wu = 1, Object.defineProperty(ls, "__esModule", { value: !0 });
  const t = /* @__PURE__ */ ce(), r = {
    keyword: "format",
    type: ["number", "string"],
    schemaType: "string",
    $data: !0,
    error: {
      message: ({ schemaCode: n }) => (0, t.str)`must match format "${n}"`,
      params: ({ schemaCode: n }) => (0, t._)`{format: ${n}}`
    },
    code(n, s) {
      const { gen: i, data: o, $data: a, schema: c, schemaCode: u, it: l } = n, { opts: h, errSchemaPath: p, schemaEnv: g, self: S } = l;
      if (!h.validateFormats)
        return;
      a ? k() : y();
      function k() {
        const b = i.scopeValue("formats", {
          ref: S.formats,
          code: h.code.formats
        }), m = i.const("fDef", (0, t._)`${b}[${u}]`), w = i.let("fType"), $ = i.let("format");
        i.if((0, t._)`typeof ${m} == "object" && !(${m} instanceof RegExp)`, () => i.assign(w, (0, t._)`${m}.type || "string"`).assign($, (0, t._)`${m}.validate`), () => i.assign(w, (0, t._)`"string"`).assign($, m)), n.fail$data((0, t.or)(d(), f()));
        function d() {
          return h.strictSchema === !1 ? t.nil : (0, t._)`${u} && !${$}`;
        }
        function f() {
          const _ = g.$async ? (0, t._)`(${m}.async ? await ${$}(${o}) : ${$}(${o}))` : (0, t._)`${$}(${o})`, E = (0, t._)`(typeof ${$} == "function" ? ${_} : ${$}.test(${o}))`;
          return (0, t._)`${$} && ${$} !== true && ${w} === ${s} && !${E}`;
        }
      }
      function y() {
        const b = S.formats[c];
        if (!b) {
          d();
          return;
        }
        if (b === !0)
          return;
        const [m, w, $] = f(b);
        m === s && n.pass(_());
        function d() {
          if (h.strictSchema === !1) {
            S.logger.warn(E());
            return;
          }
          throw new Error(E());
          function E() {
            return `unknown format "${c}" ignored in schema at path "${p}"`;
          }
        }
        function f(E) {
          const z = E instanceof RegExp ? (0, t.regexpCode)(E) : h.code.formats ? (0, t._)`${h.code.formats}${(0, t.getProperty)(c)}` : void 0, C = i.scopeValue("formats", { key: c, ref: E, code: z });
          return typeof E == "object" && !(E instanceof RegExp) ? [E.type || "string", E.validate, (0, t._)`${C}.validate`] : ["string", E, C];
        }
        function _() {
          if (typeof b == "object" && !(b instanceof RegExp) && b.async) {
            if (!g.$async)
              throw new Error("async format in sync schema");
            return (0, t._)`await ${$}(${o})`;
          }
          return typeof w == "function" ? (0, t._)`${$}(${o})` : (0, t._)`${$}.test(${o})`;
        }
      }
    }
  };
  return ls.default = r, ls;
}
var Gu;
function P0() {
  if (Gu) return us;
  Gu = 1, Object.defineProperty(us, "__esModule", { value: !0 });
  const e = [(/* @__PURE__ */ I0()).default];
  return us.default = e, us;
}
var Kt = {}, Ju;
function C0() {
  return Ju || (Ju = 1, Object.defineProperty(Kt, "__esModule", { value: !0 }), Kt.contentVocabulary = Kt.metadataVocabulary = void 0, Kt.metadataVocabulary = [
    "title",
    "description",
    "default",
    "deprecated",
    "readOnly",
    "writeOnly",
    "examples"
  ], Kt.contentVocabulary = [
    "contentMediaType",
    "contentEncoding",
    "contentSchema"
  ]), Kt;
}
var Ku;
function O0() {
  if (Ku) return Nn;
  Ku = 1, Object.defineProperty(Nn, "__esModule", { value: !0 });
  const t = /* @__PURE__ */ t0(), e = /* @__PURE__ */ f0(), r = /* @__PURE__ */ R0(), n = /* @__PURE__ */ P0(), s = /* @__PURE__ */ C0(), i = [
    t.default,
    e.default,
    (0, r.default)(),
    n.default,
    s.metadataVocabulary,
    s.contentVocabulary
  ];
  return Nn.default = i, Nn;
}
var ds = {}, Fr = {}, Qu;
function A0() {
  if (Qu) return Fr;
  Qu = 1, Object.defineProperty(Fr, "__esModule", { value: !0 }), Fr.DiscrError = void 0;
  var t;
  return (function(e) {
    e.Tag = "tag", e.Mapping = "mapping";
  })(t || (Fr.DiscrError = t = {})), Fr;
}
var Yu;
function N0() {
  if (Yu) return ds;
  Yu = 1, Object.defineProperty(ds, "__esModule", { value: !0 });
  const t = /* @__PURE__ */ ce(), e = /* @__PURE__ */ A0(), r = /* @__PURE__ */ ya(), n = /* @__PURE__ */ oi(), s = /* @__PURE__ */ fe(), o = {
    keyword: "discriminator",
    type: "object",
    schemaType: "object",
    error: {
      message: ({ params: { discrError: a, tagName: c } }) => a === e.DiscrError.Tag ? `tag "${c}" must be string` : `value of tag "${c}" must be in oneOf`,
      params: ({ params: { discrError: a, tag: c, tagName: u } }) => (0, t._)`{error: ${a}, tag: ${u}, tagValue: ${c}}`
    },
    code(a) {
      const { gen: c, data: u, schema: l, parentSchema: h, it: p } = a, { oneOf: g } = h;
      if (!p.opts.discriminator)
        throw new Error("discriminator: requires discriminator option");
      const S = l.propertyName;
      if (typeof S != "string")
        throw new Error("discriminator: requires propertyName");
      if (l.mapping)
        throw new Error("discriminator: mapping is not supported");
      if (!g)
        throw new Error("discriminator: requires oneOf keyword");
      const k = c.let("valid", !1), y = c.const("tag", (0, t._)`${u}${(0, t.getProperty)(S)}`);
      c.if((0, t._)`typeof ${y} == "string"`, () => b(), () => a.error(!1, { discrError: e.DiscrError.Tag, tag: y, tagName: S })), a.ok(k);
      function b() {
        const $ = w();
        c.if(!1);
        for (const d in $)
          c.elseIf((0, t._)`${y} === ${d}`), c.assign(k, m($[d]));
        c.else(), a.error(!1, { discrError: e.DiscrError.Mapping, tag: y, tagName: S }), c.endIf();
      }
      function m($) {
        const d = c.name("valid"), f = a.subschema({ keyword: "oneOf", schemaProp: $ }, d);
        return a.mergeEvaluated(f, t.Name), d;
      }
      function w() {
        var $;
        const d = {}, f = E(h);
        let _ = !0;
        for (let P = 0; P < g.length; P++) {
          let j = g[P];
          if (j?.$ref && !(0, s.schemaHasRulesButRef)(j, p.self.RULES)) {
            const q = j.$ref;
            if (j = r.resolveRef.call(p.self, p.schemaEnv.root, p.baseId, q), j instanceof r.SchemaEnv && (j = j.schema), j === void 0)
              throw new n.default(p.opts.uriResolver, p.baseId, q);
          }
          const O = ($ = j?.properties) === null || $ === void 0 ? void 0 : $[S];
          if (typeof O != "object")
            throw new Error(`discriminator: oneOf subschemas (or referenced schemas) must have "properties/${S}"`);
          _ = _ && (f || E(j)), z(O, P);
        }
        if (!_)
          throw new Error(`discriminator: "${S}" must be required`);
        return d;
        function E({ required: P }) {
          return Array.isArray(P) && P.includes(S);
        }
        function z(P, j) {
          if (P.const)
            C(P.const, j);
          else if (P.enum)
            for (const O of P.enum)
              C(O, j);
          else
            throw new Error(`discriminator: "properties/${S}" must have "const" or "enum"`);
        }
        function C(P, j) {
          if (typeof P != "string" || P in d)
            throw new Error(`discriminator: "${S}" values must be unique strings`);
          d[P] = j;
        }
      }
    }
  };
  return ds.default = o, ds;
}
const x0 = "http://json-schema.org/draft-07/schema#", z0 = "http://json-schema.org/draft-07/schema#", j0 = "Core schema meta-schema", M0 = { schemaArray: { type: "array", minItems: 1, items: { $ref: "#" } }, nonNegativeInteger: { type: "integer", minimum: 0 }, nonNegativeIntegerDefault0: { allOf: [{ $ref: "#/definitions/nonNegativeInteger" }, { default: 0 }] }, simpleTypes: { enum: ["array", "boolean", "integer", "null", "number", "object", "string"] }, stringArray: { type: "array", items: { type: "string" }, uniqueItems: !0, default: [] } }, q0 = ["object", "boolean"], U0 = { $id: { type: "string", format: "uri-reference" }, $schema: { type: "string", format: "uri" }, $ref: { type: "string", format: "uri-reference" }, $comment: { type: "string" }, title: { type: "string" }, description: { type: "string" }, default: !0, readOnly: { type: "boolean", default: !1 }, examples: { type: "array", items: !0 }, multipleOf: { type: "number", exclusiveMinimum: 0 }, maximum: { type: "number" }, exclusiveMaximum: { type: "number" }, minimum: { type: "number" }, exclusiveMinimum: { type: "number" }, maxLength: { $ref: "#/definitions/nonNegativeInteger" }, minLength: { $ref: "#/definitions/nonNegativeIntegerDefault0" }, pattern: { type: "string", format: "regex" }, additionalItems: { $ref: "#" }, items: { anyOf: [{ $ref: "#" }, { $ref: "#/definitions/schemaArray" }], default: !0 }, maxItems: { $ref: "#/definitions/nonNegativeInteger" }, minItems: { $ref: "#/definitions/nonNegativeIntegerDefault0" }, uniqueItems: { type: "boolean", default: !1 }, contains: { $ref: "#" }, maxProperties: { $ref: "#/definitions/nonNegativeInteger" }, minProperties: { $ref: "#/definitions/nonNegativeIntegerDefault0" }, required: { $ref: "#/definitions/stringArray" }, additionalProperties: { $ref: "#" }, definitions: { type: "object", additionalProperties: { $ref: "#" }, default: {} }, properties: { type: "object", additionalProperties: { $ref: "#" }, default: {} }, patternProperties: { type: "object", additionalProperties: { $ref: "#" }, propertyNames: { format: "regex" }, default: {} }, dependencies: { type: "object", additionalProperties: { anyOf: [{ $ref: "#" }, { $ref: "#/definitions/stringArray" }] } }, propertyNames: { $ref: "#" }, const: !0, enum: { type: "array", items: !0, minItems: 1, uniqueItems: !0 }, type: { anyOf: [{ $ref: "#/definitions/simpleTypes" }, { type: "array", items: { $ref: "#/definitions/simpleTypes" }, minItems: 1, uniqueItems: !0 }] }, format: { type: "string" }, contentMediaType: { type: "string" }, contentEncoding: { type: "string" }, if: { $ref: "#" }, then: { $ref: "#" }, else: { $ref: "#" }, allOf: { $ref: "#/definitions/schemaArray" }, anyOf: { $ref: "#/definitions/schemaArray" }, oneOf: { $ref: "#/definitions/schemaArray" }, not: { $ref: "#" } }, D0 = {
  $schema: x0,
  $id: z0,
  title: j0,
  definitions: M0,
  type: q0,
  properties: U0,
  default: !0
};
var Xu;
function Zh() {
  return Xu || (Xu = 1, (function(t, e) {
    Object.defineProperty(e, "__esModule", { value: !0 }), e.MissingRefError = e.ValidationError = e.CodeGen = e.Name = e.nil = e.stringify = e.str = e._ = e.KeywordCxt = e.Ajv = void 0;
    const r = /* @__PURE__ */ Yb(), n = /* @__PURE__ */ O0(), s = /* @__PURE__ */ N0(), i = D0, o = ["/properties"], a = "http://json-schema.org/draft-07/schema";
    class c extends r.default {
      _addVocabularies() {
        super._addVocabularies(), n.default.forEach((S) => this.addVocabulary(S)), this.opts.discriminator && this.addKeyword(s.default);
      }
      _addDefaultMetaSchema() {
        if (super._addDefaultMetaSchema(), !this.opts.meta)
          return;
        const S = this.opts.$data ? this.$dataMetaSchema(i, o) : i;
        this.addMetaSchema(S, a, !1), this.refs["http://json-schema.org/schema"] = a;
      }
      defaultMeta() {
        return this.opts.defaultMeta = super.defaultMeta() || (this.getSchema(a) ? a : void 0);
      }
    }
    e.Ajv = c, t.exports = e = c, t.exports.Ajv = c, Object.defineProperty(e, "__esModule", { value: !0 }), e.default = c;
    var u = /* @__PURE__ */ ii();
    Object.defineProperty(e, "KeywordCxt", { enumerable: !0, get: function() {
      return u.KeywordCxt;
    } });
    var l = /* @__PURE__ */ ce();
    Object.defineProperty(e, "_", { enumerable: !0, get: function() {
      return l._;
    } }), Object.defineProperty(e, "str", { enumerable: !0, get: function() {
      return l.str;
    } }), Object.defineProperty(e, "stringify", { enumerable: !0, get: function() {
      return l.stringify;
    } }), Object.defineProperty(e, "nil", { enumerable: !0, get: function() {
      return l.nil;
    } }), Object.defineProperty(e, "Name", { enumerable: !0, get: function() {
      return l.Name;
    } }), Object.defineProperty(e, "CodeGen", { enumerable: !0, get: function() {
      return l.CodeGen;
    } });
    var h = /* @__PURE__ */ _a();
    Object.defineProperty(e, "ValidationError", { enumerable: !0, get: function() {
      return h.default;
    } });
    var p = /* @__PURE__ */ oi();
    Object.defineProperty(e, "MissingRefError", { enumerable: !0, get: function() {
      return p.default;
    } });
  })(In, In.exports)), In.exports;
}
var L0 = /* @__PURE__ */ Zh();
const Z0 = /* @__PURE__ */ ga(L0);
var hs = { exports: {} }, zi = {}, el;
function H0() {
  return el || (el = 1, (function(t) {
    Object.defineProperty(t, "__esModule", { value: !0 }), t.formatNames = t.fastFormats = t.fullFormats = void 0;
    function e(P, j) {
      return { validate: P, compare: j };
    }
    t.fullFormats = {
      // date: http://tools.ietf.org/html/rfc3339#section-5.6
      date: e(i, o),
      // date-time: http://tools.ietf.org/html/rfc3339#section-5.6
      time: e(c(!0), u),
      "date-time": e(p(!0), g),
      "iso-time": e(c(), l),
      "iso-date-time": e(p(), S),
      // duration: https://tools.ietf.org/html/rfc3339#appendix-A
      duration: /^P(?!$)((\d+Y)?(\d+M)?(\d+D)?(T(?=\d)(\d+H)?(\d+M)?(\d+S)?)?|(\d+W)?)$/,
      uri: b,
      "uri-reference": /^(?:[a-z][a-z0-9+\-.]*:)?(?:\/?\/(?:(?:[a-z0-9\-._~!$&'()*+,;=:]|%[0-9a-f]{2})*@)?(?:\[(?:(?:(?:(?:[0-9a-f]{1,4}:){6}|::(?:[0-9a-f]{1,4}:){5}|(?:[0-9a-f]{1,4})?::(?:[0-9a-f]{1,4}:){4}|(?:(?:[0-9a-f]{1,4}:){0,1}[0-9a-f]{1,4})?::(?:[0-9a-f]{1,4}:){3}|(?:(?:[0-9a-f]{1,4}:){0,2}[0-9a-f]{1,4})?::(?:[0-9a-f]{1,4}:){2}|(?:(?:[0-9a-f]{1,4}:){0,3}[0-9a-f]{1,4})?::[0-9a-f]{1,4}:|(?:(?:[0-9a-f]{1,4}:){0,4}[0-9a-f]{1,4})?::)(?:[0-9a-f]{1,4}:[0-9a-f]{1,4}|(?:(?:25[0-5]|2[0-4]\d|[01]?\d\d?)\.){3}(?:25[0-5]|2[0-4]\d|[01]?\d\d?))|(?:(?:[0-9a-f]{1,4}:){0,5}[0-9a-f]{1,4})?::[0-9a-f]{1,4}|(?:(?:[0-9a-f]{1,4}:){0,6}[0-9a-f]{1,4})?::)|[Vv][0-9a-f]+\.[a-z0-9\-._~!$&'()*+,;=:]+)\]|(?:(?:25[0-5]|2[0-4]\d|[01]?\d\d?)\.){3}(?:25[0-5]|2[0-4]\d|[01]?\d\d?)|(?:[a-z0-9\-._~!$&'"()*+,;=]|%[0-9a-f]{2})*)(?::\d*)?(?:\/(?:[a-z0-9\-._~!$&'"()*+,;=:@]|%[0-9a-f]{2})*)*|\/(?:(?:[a-z0-9\-._~!$&'"()*+,;=:@]|%[0-9a-f]{2})+(?:\/(?:[a-z0-9\-._~!$&'"()*+,;=:@]|%[0-9a-f]{2})*)*)?|(?:[a-z0-9\-._~!$&'"()*+,;=:@]|%[0-9a-f]{2})+(?:\/(?:[a-z0-9\-._~!$&'"()*+,;=:@]|%[0-9a-f]{2})*)*)?(?:\?(?:[a-z0-9\-._~!$&'"()*+,;=:@/?]|%[0-9a-f]{2})*)?(?:#(?:[a-z0-9\-._~!$&'"()*+,;=:@/?]|%[0-9a-f]{2})*)?$/i,
      // uri-template: https://tools.ietf.org/html/rfc6570
      "uri-template": /^(?:(?:[^\x00-\x20"'<>%\\^`{|}]|%[0-9a-f]{2})|\{[+#./;?&=,!@|]?(?:[a-z0-9_]|%[0-9a-f]{2})+(?::[1-9][0-9]{0,3}|\*)?(?:,(?:[a-z0-9_]|%[0-9a-f]{2})+(?::[1-9][0-9]{0,3}|\*)?)*\})*$/i,
      // For the source: https://gist.github.com/dperini/729294
      // For test cases: https://mathiasbynens.be/demo/url-regex
      url: /^(?:https?|ftp):\/\/(?:\S+(?::\S*)?@)?(?:(?!(?:10|127)(?:\.\d{1,3}){3})(?!(?:169\.254|192\.168)(?:\.\d{1,3}){2})(?!172\.(?:1[6-9]|2\d|3[0-1])(?:\.\d{1,3}){2})(?:[1-9]\d?|1\d\d|2[01]\d|22[0-3])(?:\.(?:1?\d{1,2}|2[0-4]\d|25[0-5])){2}(?:\.(?:[1-9]\d?|1\d\d|2[0-4]\d|25[0-4]))|(?:(?:[a-z0-9\u{00a1}-\u{ffff}]+-)*[a-z0-9\u{00a1}-\u{ffff}]+)(?:\.(?:[a-z0-9\u{00a1}-\u{ffff}]+-)*[a-z0-9\u{00a1}-\u{ffff}]+)*(?:\.(?:[a-z\u{00a1}-\u{ffff}]{2,})))(?::\d{2,5})?(?:\/[^\s]*)?$/iu,
      email: /^[a-z0-9!#$%&'*+/=?^_`{|}~-]+(?:\.[a-z0-9!#$%&'*+/=?^_`{|}~-]+)*@(?:[a-z0-9](?:[a-z0-9-]*[a-z0-9])?\.)+[a-z0-9](?:[a-z0-9-]*[a-z0-9])?$/i,
      hostname: /^(?=.{1,253}\.?$)[a-z0-9](?:[a-z0-9-]{0,61}[a-z0-9])?(?:\.[a-z0-9](?:[-0-9a-z]{0,61}[0-9a-z])?)*\.?$/i,
      // optimized https://www.safaribooksonline.com/library/view/regular-expressions-cookbook/9780596802837/ch07s16.html
      ipv4: /^(?:(?:25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)\.){3}(?:25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)$/,
      ipv6: /^((([0-9a-f]{1,4}:){7}([0-9a-f]{1,4}|:))|(([0-9a-f]{1,4}:){6}(:[0-9a-f]{1,4}|((25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)(\.(25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)){3})|:))|(([0-9a-f]{1,4}:){5}(((:[0-9a-f]{1,4}){1,2})|:((25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)(\.(25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)){3})|:))|(([0-9a-f]{1,4}:){4}(((:[0-9a-f]{1,4}){1,3})|((:[0-9a-f]{1,4})?:((25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)(\.(25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)){3}))|:))|(([0-9a-f]{1,4}:){3}(((:[0-9a-f]{1,4}){1,4})|((:[0-9a-f]{1,4}){0,2}:((25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)(\.(25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)){3}))|:))|(([0-9a-f]{1,4}:){2}(((:[0-9a-f]{1,4}){1,5})|((:[0-9a-f]{1,4}){0,3}:((25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)(\.(25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)){3}))|:))|(([0-9a-f]{1,4}:){1}(((:[0-9a-f]{1,4}){1,6})|((:[0-9a-f]{1,4}){0,4}:((25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)(\.(25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)){3}))|:))|(:(((:[0-9a-f]{1,4}){1,7})|((:[0-9a-f]{1,4}){0,5}:((25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)(\.(25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)){3}))|:)))$/i,
      regex: C,
      // uuid: http://tools.ietf.org/html/rfc4122
      uuid: /^(?:urn:uuid:)?[0-9a-f]{8}-(?:[0-9a-f]{4}-){3}[0-9a-f]{12}$/i,
      // JSON-pointer: https://tools.ietf.org/html/rfc6901
      // uri fragment: https://tools.ietf.org/html/rfc3986#appendix-A
      "json-pointer": /^(?:\/(?:[^~/]|~0|~1)*)*$/,
      "json-pointer-uri-fragment": /^#(?:\/(?:[a-z0-9_\-.!$&'()*+,;:=@]|%[0-9a-f]{2}|~0|~1)*)*$/i,
      // relative JSON-pointer: http://tools.ietf.org/html/draft-luff-relative-json-pointer-00
      "relative-json-pointer": /^(?:0|[1-9][0-9]*)(?:#|(?:\/(?:[^~/]|~0|~1)*)*)$/,
      // the following formats are used by the openapi specification: https://spec.openapis.org/oas/v3.0.0#data-types
      // byte: https://github.com/miguelmota/is-base64
      byte: w,
      // signed 32 bit integer
      int32: { type: "number", validate: f },
      // signed 64 bit integer
      int64: { type: "number", validate: _ },
      // C-type float
      float: { type: "number", validate: E },
      // C-type double
      double: { type: "number", validate: E },
      // hint to the UI to hide input strings
      password: !0,
      // unchecked string payload
      binary: !0
    }, t.fastFormats = {
      ...t.fullFormats,
      date: e(/^\d\d\d\d-[0-1]\d-[0-3]\d$/, o),
      time: e(/^(?:[0-2]\d:[0-5]\d:[0-5]\d|23:59:60)(?:\.\d+)?(?:z|[+-]\d\d(?::?\d\d)?)$/i, u),
      "date-time": e(/^\d\d\d\d-[0-1]\d-[0-3]\dt(?:[0-2]\d:[0-5]\d:[0-5]\d|23:59:60)(?:\.\d+)?(?:z|[+-]\d\d(?::?\d\d)?)$/i, g),
      "iso-time": e(/^(?:[0-2]\d:[0-5]\d:[0-5]\d|23:59:60)(?:\.\d+)?(?:z|[+-]\d\d(?::?\d\d)?)?$/i, l),
      "iso-date-time": e(/^\d\d\d\d-[0-1]\d-[0-3]\d[t\s](?:[0-2]\d:[0-5]\d:[0-5]\d|23:59:60)(?:\.\d+)?(?:z|[+-]\d\d(?::?\d\d)?)?$/i, S),
      // uri: https://github.com/mafintosh/is-my-json-valid/blob/master/formats.js
      uri: /^(?:[a-z][a-z0-9+\-.]*:)(?:\/?\/)?[^\s]*$/i,
      "uri-reference": /^(?:(?:[a-z][a-z0-9+\-.]*:)?\/?\/)?(?:[^\\\s#][^\s#]*)?(?:#[^\\\s]*)?$/i,
      // email (sources from jsen validator):
      // http://stackoverflow.com/questions/201323/using-a-regular-expression-to-validate-an-email-address#answer-8829363
      // http://www.w3.org/TR/html5/forms.html#valid-e-mail-address (search for 'wilful violation')
      email: /^[a-z0-9.!#$%&'*+/=?^_`{|}~-]+@[a-z0-9](?:[a-z0-9-]{0,61}[a-z0-9])?(?:\.[a-z0-9](?:[a-z0-9-]{0,61}[a-z0-9])?)*$/i
    }, t.formatNames = Object.keys(t.fullFormats);
    function r(P) {
      return P % 4 === 0 && (P % 100 !== 0 || P % 400 === 0);
    }
    const n = /^(\d\d\d\d)-(\d\d)-(\d\d)$/, s = [0, 31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31];
    function i(P) {
      const j = n.exec(P);
      if (!j)
        return !1;
      const O = +j[1], q = +j[2], se = +j[3];
      return q >= 1 && q <= 12 && se >= 1 && se <= (q === 2 && r(O) ? 29 : s[q]);
    }
    function o(P, j) {
      if (P && j)
        return P > j ? 1 : P < j ? -1 : 0;
    }
    const a = /^(\d\d):(\d\d):(\d\d(?:\.\d+)?)(z|([+-])(\d\d)(?::?(\d\d))?)?$/i;
    function c(P) {
      return function(O) {
        const q = a.exec(O);
        if (!q)
          return !1;
        const se = +q[1], Ee = +q[2], be = +q[3], oe = q[4], Me = q[5] === "-" ? -1 : 1, Z = +(q[6] || 0), A = +(q[7] || 0);
        if (Z > 23 || A > 59 || P && !oe)
          return !1;
        if (se <= 23 && Ee <= 59 && be < 60)
          return !0;
        const D = Ee - A * Me, x = se - Z * Me - (D < 0 ? 1 : 0);
        return (x === 23 || x === -1) && (D === 59 || D === -1) && be < 61;
      };
    }
    function u(P, j) {
      if (!(P && j))
        return;
      const O = (/* @__PURE__ */ new Date("2020-01-01T" + P)).valueOf(), q = (/* @__PURE__ */ new Date("2020-01-01T" + j)).valueOf();
      if (O && q)
        return O - q;
    }
    function l(P, j) {
      if (!(P && j))
        return;
      const O = a.exec(P), q = a.exec(j);
      if (O && q)
        return P = O[1] + O[2] + O[3], j = q[1] + q[2] + q[3], P > j ? 1 : P < j ? -1 : 0;
    }
    const h = /t|\s/i;
    function p(P) {
      const j = c(P);
      return function(q) {
        const se = q.split(h);
        return se.length === 2 && i(se[0]) && j(se[1]);
      };
    }
    function g(P, j) {
      if (!(P && j))
        return;
      const O = new Date(P).valueOf(), q = new Date(j).valueOf();
      if (O && q)
        return O - q;
    }
    function S(P, j) {
      if (!(P && j))
        return;
      const [O, q] = P.split(h), [se, Ee] = j.split(h), be = o(O, se);
      if (be !== void 0)
        return be || u(q, Ee);
    }
    const k = /\/|:/, y = /^(?:[a-z][a-z0-9+\-.]*:)(?:\/?\/(?:(?:[a-z0-9\-._~!$&'()*+,;=:]|%[0-9a-f]{2})*@)?(?:\[(?:(?:(?:(?:[0-9a-f]{1,4}:){6}|::(?:[0-9a-f]{1,4}:){5}|(?:[0-9a-f]{1,4})?::(?:[0-9a-f]{1,4}:){4}|(?:(?:[0-9a-f]{1,4}:){0,1}[0-9a-f]{1,4})?::(?:[0-9a-f]{1,4}:){3}|(?:(?:[0-9a-f]{1,4}:){0,2}[0-9a-f]{1,4})?::(?:[0-9a-f]{1,4}:){2}|(?:(?:[0-9a-f]{1,4}:){0,3}[0-9a-f]{1,4})?::[0-9a-f]{1,4}:|(?:(?:[0-9a-f]{1,4}:){0,4}[0-9a-f]{1,4})?::)(?:[0-9a-f]{1,4}:[0-9a-f]{1,4}|(?:(?:25[0-5]|2[0-4]\d|[01]?\d\d?)\.){3}(?:25[0-5]|2[0-4]\d|[01]?\d\d?))|(?:(?:[0-9a-f]{1,4}:){0,5}[0-9a-f]{1,4})?::[0-9a-f]{1,4}|(?:(?:[0-9a-f]{1,4}:){0,6}[0-9a-f]{1,4})?::)|[Vv][0-9a-f]+\.[a-z0-9\-._~!$&'()*+,;=:]+)\]|(?:(?:25[0-5]|2[0-4]\d|[01]?\d\d?)\.){3}(?:25[0-5]|2[0-4]\d|[01]?\d\d?)|(?:[a-z0-9\-._~!$&'()*+,;=]|%[0-9a-f]{2})*)(?::\d*)?(?:\/(?:[a-z0-9\-._~!$&'()*+,;=:@]|%[0-9a-f]{2})*)*|\/(?:(?:[a-z0-9\-._~!$&'()*+,;=:@]|%[0-9a-f]{2})+(?:\/(?:[a-z0-9\-._~!$&'()*+,;=:@]|%[0-9a-f]{2})*)*)?|(?:[a-z0-9\-._~!$&'()*+,;=:@]|%[0-9a-f]{2})+(?:\/(?:[a-z0-9\-._~!$&'()*+,;=:@]|%[0-9a-f]{2})*)*)(?:\?(?:[a-z0-9\-._~!$&'()*+,;=:@/?]|%[0-9a-f]{2})*)?(?:#(?:[a-z0-9\-._~!$&'()*+,;=:@/?]|%[0-9a-f]{2})*)?$/i;
    function b(P) {
      return k.test(P) && y.test(P);
    }
    const m = /^(?:[A-Za-z0-9+/]{4})*(?:[A-Za-z0-9+/]{2}==|[A-Za-z0-9+/]{3}=)?$/gm;
    function w(P) {
      return m.lastIndex = 0, m.test(P);
    }
    const $ = -2147483648, d = 2 ** 31 - 1;
    function f(P) {
      return Number.isInteger(P) && P <= d && P >= $;
    }
    function _(P) {
      return Number.isInteger(P);
    }
    function E() {
      return !0;
    }
    const z = /[^\\]\\Z/;
    function C(P) {
      if (z.test(P))
        return !1;
      try {
        return new RegExp(P), !0;
      } catch {
        return !1;
      }
    }
  })(zi)), zi;
}
var ji = {}, tl;
function F0() {
  return tl || (tl = 1, (function(t) {
    Object.defineProperty(t, "__esModule", { value: !0 }), t.formatLimitDefinition = void 0;
    const e = /* @__PURE__ */ Zh(), r = /* @__PURE__ */ ce(), n = r.operators, s = {
      formatMaximum: { okStr: "<=", ok: n.LTE, fail: n.GT },
      formatMinimum: { okStr: ">=", ok: n.GTE, fail: n.LT },
      formatExclusiveMaximum: { okStr: "<", ok: n.LT, fail: n.GTE },
      formatExclusiveMinimum: { okStr: ">", ok: n.GT, fail: n.LTE }
    }, i = {
      message: ({ keyword: a, schemaCode: c }) => (0, r.str)`should be ${s[a].okStr} ${c}`,
      params: ({ keyword: a, schemaCode: c }) => (0, r._)`{comparison: ${s[a].okStr}, limit: ${c}}`
    };
    t.formatLimitDefinition = {
      keyword: Object.keys(s),
      type: "string",
      schemaType: "string",
      $data: !0,
      error: i,
      code(a) {
        const { gen: c, data: u, schemaCode: l, keyword: h, it: p } = a, { opts: g, self: S } = p;
        if (!g.validateFormats)
          return;
        const k = new e.KeywordCxt(p, S.RULES.all.format.definition, "format");
        k.$data ? y() : b();
        function y() {
          const w = c.scopeValue("formats", {
            ref: S.formats,
            code: g.code.formats
          }), $ = c.const("fmt", (0, r._)`${w}[${k.schemaCode}]`);
          a.fail$data((0, r.or)((0, r._)`typeof ${$} != "object"`, (0, r._)`${$} instanceof RegExp`, (0, r._)`typeof ${$}.compare != "function"`, m($)));
        }
        function b() {
          const w = k.schema, $ = S.formats[w];
          if (!$ || $ === !0)
            return;
          if (typeof $ != "object" || $ instanceof RegExp || typeof $.compare != "function")
            throw new Error(`"${h}": format "${w}" does not define "compare" function`);
          const d = c.scopeValue("formats", {
            key: w,
            ref: $,
            code: g.code.formats ? (0, r._)`${g.code.formats}${(0, r.getProperty)(w)}` : void 0
          });
          a.fail$data(m(d));
        }
        function m(w) {
          return (0, r._)`${w}.compare(${u}, ${l}) ${s[h].fail} 0`;
        }
      },
      dependencies: ["format"]
    };
    const o = (a) => (a.addKeyword(t.formatLimitDefinition), a);
    t.default = o;
  })(ji)), ji;
}
var rl;
function V0() {
  return rl || (rl = 1, (function(t, e) {
    Object.defineProperty(e, "__esModule", { value: !0 });
    const r = H0(), n = F0(), s = /* @__PURE__ */ ce(), i = new s.Name("fullFormats"), o = new s.Name("fastFormats"), a = (u, l = { keywords: !0 }) => {
      if (Array.isArray(l))
        return c(u, l, r.fullFormats, i), u;
      const [h, p] = l.mode === "fast" ? [r.fastFormats, o] : [r.fullFormats, i], g = l.formats || r.formatNames;
      return c(u, g, h, p), l.keywords && (0, n.default)(u), u;
    };
    a.get = (u, l = "full") => {
      const p = (l === "fast" ? r.fastFormats : r.fullFormats)[u];
      if (!p)
        throw new Error(`Unknown format "${u}"`);
      return p;
    };
    function c(u, l, h, p) {
      var g, S;
      (g = (S = u.opts.code).formats) !== null && g !== void 0 || (S.formats = (0, s._)`require("ajv-formats/dist/formats").${p}`);
      for (const k of l)
        u.addFormat(k, h[k]);
    }
    t.exports = e = a, Object.defineProperty(e, "__esModule", { value: !0 }), e.default = a;
  })(hs, hs.exports)), hs.exports;
}
var B0 = V0();
const W0 = /* @__PURE__ */ ga(B0);
function G0() {
  const t = new Z0({
    strict: !1,
    validateFormats: !0,
    validateSchema: !1,
    allErrors: !0
  });
  return W0(t), t;
}
class Hh {
  /**
   * Create an AJV validator
   *
   * @param ajv - Optional pre-configured AJV instance. If not provided, a default instance will be created.
   *
   * @example
   * ```typescript
   * // Use default configuration (recommended for most cases)
   * import { AjvJsonSchemaValidator } from '@modelcontextprotocol/sdk/validation/ajv';
   * const validator = new AjvJsonSchemaValidator();
   *
   * // Or provide custom AJV instance for advanced configuration
   * import { Ajv } from 'ajv';
   * import addFormats from 'ajv-formats';
   *
   * const ajv = new Ajv({ validateFormats: true });
   * addFormats(ajv);
   * const validator = new AjvJsonSchemaValidator(ajv);
   * ```
   */
  constructor(e) {
    this._ajv = e ?? G0();
  }
  /**
   * Create a validator for the given JSON Schema
   *
   * The validator is compiled once and can be reused multiple times.
   * If the schema has an $id, it will be cached by AJV automatically.
   *
   * @param schema - Standard JSON Schema object
   * @returns A validator function that validates input data
   */
  getValidator(e) {
    const r = "$id" in e && typeof e.$id == "string" ? this._ajv.getSchema(e.$id) ?? this._ajv.compile(e) : this._ajv.compile(e);
    return (n) => r(n) ? {
      valid: !0,
      data: n,
      errorMessage: void 0
    } : {
      valid: !1,
      data: void 0,
      errorMessage: this._ajv.errorsText(r.errors)
    };
  }
}
class J0 {
  constructor(e) {
    this._server = e;
  }
  /**
   * Sends a request and returns an AsyncGenerator that yields response messages.
   * The generator is guaranteed to end with either a 'result' or 'error' message.
   *
   * This method provides streaming access to request processing, allowing you to
   * observe intermediate task status updates for task-augmented requests.
   *
   * @param request - The request to send
   * @param resultSchema - Zod schema for validating the result
   * @param options - Optional request options (timeout, signal, task creation params, etc.)
   * @returns AsyncGenerator that yields ResponseMessage objects
   *
   * @experimental
   */
  requestStream(e, r, n) {
    return this._server.requestStream(e, r, n);
  }
  /**
   * Sends a sampling request and returns an AsyncGenerator that yields response messages.
   * The generator is guaranteed to end with either a 'result' or 'error' message.
   *
   * For task-augmented requests, yields 'taskCreated' and 'taskStatus' messages
   * before the final result.
   *
   * @example
   * ```typescript
   * const stream = server.experimental.tasks.createMessageStream({
   *     messages: [{ role: 'user', content: { type: 'text', text: 'Hello' } }],
   *     maxTokens: 100
   * }, {
   *     onprogress: (progress) => {
   *         // Handle streaming tokens via progress notifications
   *         console.log('Progress:', progress.message);
   *     }
   * });
   *
   * for await (const message of stream) {
   *     switch (message.type) {
   *         case 'taskCreated':
   *             console.log('Task created:', message.task.taskId);
   *             break;
   *         case 'taskStatus':
   *             console.log('Task status:', message.task.status);
   *             break;
   *         case 'result':
   *             console.log('Final result:', message.result);
   *             break;
   *         case 'error':
   *             console.error('Error:', message.error);
   *             break;
   *     }
   * }
   * ```
   *
   * @param params - The sampling request parameters
   * @param options - Optional request options (timeout, signal, task creation params, onprogress, etc.)
   * @returns AsyncGenerator that yields ResponseMessage objects
   *
   * @experimental
   */
  createMessageStream(e, r) {
    const n = this._server.getClientCapabilities();
    if ((e.tools || e.toolChoice) && !n?.sampling?.tools)
      throw new Error("Client does not support sampling tools capability.");
    if (e.messages.length > 0) {
      const s = e.messages[e.messages.length - 1], i = Array.isArray(s.content) ? s.content : [s.content], o = i.some((l) => l.type === "tool_result"), a = e.messages.length > 1 ? e.messages[e.messages.length - 2] : void 0, c = a ? Array.isArray(a.content) ? a.content : [a.content] : [], u = c.some((l) => l.type === "tool_use");
      if (o) {
        if (i.some((l) => l.type !== "tool_result"))
          throw new Error("The last message must contain only tool_result content if any is present");
        if (!u)
          throw new Error("tool_result blocks are not matching any tool_use from the previous message");
      }
      if (u) {
        const l = new Set(c.filter((p) => p.type === "tool_use").map((p) => p.id)), h = new Set(i.filter((p) => p.type === "tool_result").map((p) => p.toolUseId));
        if (l.size !== h.size || ![...l].every((p) => h.has(p)))
          throw new Error("ids of tool_result blocks and tool_use blocks from previous message do not match");
      }
    }
    return this.requestStream({
      method: "sampling/createMessage",
      params: e
    }, Xs, r);
  }
  /**
   * Sends an elicitation request and returns an AsyncGenerator that yields response messages.
   * The generator is guaranteed to end with either a 'result' or 'error' message.
   *
   * For task-augmented requests (especially URL-based elicitation), yields 'taskCreated'
   * and 'taskStatus' messages before the final result.
   *
   * @example
   * ```typescript
   * const stream = server.experimental.tasks.elicitInputStream({
   *     mode: 'url',
   *     message: 'Please authenticate',
   *     elicitationId: 'auth-123',
   *     url: 'https://example.com/auth'
   * }, {
   *     task: { ttl: 300000 } // Task-augmented for long-running auth flow
   * });
   *
   * for await (const message of stream) {
   *     switch (message.type) {
   *         case 'taskCreated':
   *             console.log('Task created:', message.task.taskId);
   *             break;
   *         case 'taskStatus':
   *             console.log('Task status:', message.task.status);
   *             break;
   *         case 'result':
   *             console.log('User action:', message.result.action);
   *             break;
   *         case 'error':
   *             console.error('Error:', message.error);
   *             break;
   *     }
   * }
   * ```
   *
   * @param params - The elicitation request parameters
   * @param options - Optional request options (timeout, signal, task creation params, etc.)
   * @returns AsyncGenerator that yields ResponseMessage objects
   *
   * @experimental
   */
  elicitInputStream(e, r) {
    const n = this._server.getClientCapabilities(), s = e.mode ?? "form";
    switch (s) {
      case "url": {
        if (!n?.elicitation?.url)
          throw new Error("Client does not support url elicitation.");
        break;
      }
      case "form": {
        if (!n?.elicitation?.form)
          throw new Error("Client does not support form elicitation.");
        break;
      }
    }
    const i = s === "form" && e.mode === void 0 ? { ...e, mode: "form" } : e;
    return this.requestStream({
      method: "elicitation/create",
      params: i
    }, rn, r);
  }
  /**
   * Gets the current status of a task.
   *
   * @param taskId - The task identifier
   * @param options - Optional request options
   * @returns The task status
   *
   * @experimental
   */
  async getTask(e, r) {
    return this._server.getTask({ taskId: e }, r);
  }
  /**
   * Retrieves the result of a completed task.
   *
   * @param taskId - The task identifier
   * @param resultSchema - Zod schema for validating the result
   * @param options - Optional request options
   * @returns The task result
   *
   * @experimental
   */
  async getTaskResult(e, r, n) {
    return this._server.getTaskResult({ taskId: e }, r, n);
  }
  /**
   * Lists tasks with optional pagination.
   *
   * @param cursor - Optional pagination cursor
   * @param options - Optional request options
   * @returns List of tasks with optional next cursor
   *
   * @experimental
   */
  async listTasks(e, r) {
    return this._server.listTasks(e ? { cursor: e } : void 0, r);
  }
  /**
   * Cancels a running task.
   *
   * @param taskId - The task identifier
   * @param options - Optional request options
   *
   * @experimental
   */
  async cancelTask(e, r) {
    return this._server.cancelTask({ taskId: e }, r);
  }
}
function Fh(t, e, r) {
  if (!t)
    throw new Error(`${r} does not support task creation (required for ${e})`);
  switch (e) {
    case "tools/call":
      if (!t.tools?.call)
        throw new Error(`${r} does not support task creation for tools/call (required for ${e})`);
      break;
  }
}
function Vh(t, e, r) {
  if (!t)
    throw new Error(`${r} does not support task creation (required for ${e})`);
  switch (e) {
    case "sampling/createMessage":
      if (!t.sampling?.createMessage)
        throw new Error(`${r} does not support task creation for sampling/createMessage (required for ${e})`);
      break;
    case "elicitation/create":
      if (!t.elicitation?.create)
        throw new Error(`${r} does not support task creation for elicitation/create (required for ${e})`);
      break;
  }
}
class K0 extends Nh {
  /**
   * Initializes this server with the given name and version information.
   */
  constructor(e, r) {
    super(r), this._serverInfo = e, this._loggingLevels = /* @__PURE__ */ new Map(), this.LOG_LEVEL_SEVERITY = new Map(Rs.options.map((n, s) => [n, s])), this.isMessageIgnored = (n, s) => {
      const i = this._loggingLevels.get(s);
      return i ? this.LOG_LEVEL_SEVERITY.get(n) < this.LOG_LEVEL_SEVERITY.get(i) : !1;
    }, this._capabilities = r?.capabilities ?? {}, this._instructions = r?.instructions, this._jsonSchemaValidator = r?.jsonSchemaValidator ?? new Hh(), this.setRequestHandler(Rd, (n) => this._oninitialize(n)), this.setNotificationHandler(Bo, () => this.oninitialized?.()), this._capabilities.logging && this.setRequestHandler(Hd, async (n, s) => {
      const i = s.sessionId || s.requestInfo?.headers["mcp-session-id"] || void 0, { level: o } = n.params, a = Rs.safeParse(o);
      return a.success && this._loggingLevels.set(i, a.data), {};
    });
  }
  /**
   * Access experimental features.
   *
   * WARNING: These APIs are experimental and may change without notice.
   *
   * @experimental
   */
  get experimental() {
    return this._experimental || (this._experimental = {
      tasks: new J0(this)
    }), this._experimental;
  }
  /**
   * Registers new capabilities. This can only be called before connecting to a transport.
   *
   * The new capabilities will be merged with any existing capabilities previously given (e.g., at initialization).
   */
  registerCapabilities(e) {
    if (this.transport)
      throw new Error("Cannot register capabilities after connecting to transport");
    this._capabilities = xh(this._capabilities, e);
  }
  /**
   * Override request handler registration to enforce server-side validation for tools/call.
   */
  setRequestHandler(e, r) {
    const s = Mr(e)?.method;
    if (!s)
      throw new Error("Schema is missing a method literal");
    const i = ri(s);
    if (typeof i != "string")
      throw new Error("Schema method literal must be a string");
    if (i === "tools/call") {
      const a = async (c, u) => {
        const l = _t(tn, c);
        if (!l.success) {
          const S = l.error instanceof Error ? l.error.message : String(l.error);
          throw new G(K.InvalidParams, `Invalid tools/call request: ${S}`);
        }
        const { params: h } = l.data, p = await Promise.resolve(r(c, u));
        if (h.task) {
          const S = _t(Er, p);
          if (!S.success) {
            const k = S.error instanceof Error ? S.error.message : String(S.error);
            throw new G(K.InvalidParams, `Invalid task creation result: ${k}`);
          }
          return S.data;
        }
        const g = _t(Sn, p);
        if (!g.success) {
          const S = g.error instanceof Error ? g.error.message : String(g.error);
          throw new G(K.InvalidParams, `Invalid tools/call result: ${S}`);
        }
        return g.data;
      };
      return super.setRequestHandler(e, a);
    }
    return super.setRequestHandler(e, r);
  }
  assertCapabilityForMethod(e) {
    switch (e) {
      case "sampling/createMessage":
        if (!this._clientCapabilities?.sampling)
          throw new Error(`Client does not support sampling (required for ${e})`);
        break;
      case "elicitation/create":
        if (!this._clientCapabilities?.elicitation)
          throw new Error(`Client does not support elicitation (required for ${e})`);
        break;
      case "roots/list":
        if (!this._clientCapabilities?.roots)
          throw new Error(`Client does not support listing roots (required for ${e})`);
        break;
    }
  }
  assertNotificationCapability(e) {
    switch (e) {
      case "notifications/message":
        if (!this._capabilities.logging)
          throw new Error(`Server does not support logging (required for ${e})`);
        break;
      case "notifications/resources/updated":
      case "notifications/resources/list_changed":
        if (!this._capabilities.resources)
          throw new Error(`Server does not support notifying about resources (required for ${e})`);
        break;
      case "notifications/tools/list_changed":
        if (!this._capabilities.tools)
          throw new Error(`Server does not support notifying of tool list changes (required for ${e})`);
        break;
      case "notifications/prompts/list_changed":
        if (!this._capabilities.prompts)
          throw new Error(`Server does not support notifying of prompt list changes (required for ${e})`);
        break;
      case "notifications/elicitation/complete":
        if (!this._clientCapabilities?.elicitation?.url)
          throw new Error(`Client does not support URL elicitation (required for ${e})`);
        break;
    }
  }
  assertRequestHandlerCapability(e) {
    if (this._capabilities)
      switch (e) {
        case "completion/complete":
          if (!this._capabilities.completions)
            throw new Error(`Server does not support completions (required for ${e})`);
          break;
        case "logging/setLevel":
          if (!this._capabilities.logging)
            throw new Error(`Server does not support logging (required for ${e})`);
          break;
        case "prompts/get":
        case "prompts/list":
          if (!this._capabilities.prompts)
            throw new Error(`Server does not support prompts (required for ${e})`);
          break;
        case "resources/list":
        case "resources/templates/list":
        case "resources/read":
          if (!this._capabilities.resources)
            throw new Error(`Server does not support resources (required for ${e})`);
          break;
        case "tools/call":
        case "tools/list":
          if (!this._capabilities.tools)
            throw new Error(`Server does not support tools (required for ${e})`);
          break;
        case "tasks/get":
        case "tasks/list":
        case "tasks/result":
        case "tasks/cancel":
          if (!this._capabilities.tasks)
            throw new Error(`Server does not support tasks capability (required for ${e})`);
          break;
      }
  }
  assertTaskCapability(e) {
    Vh(this._clientCapabilities?.tasks?.requests, e, "Client");
  }
  assertTaskHandlerCapability(e) {
    this._capabilities && Fh(this._capabilities.tasks?.requests, e, "Server");
  }
  async _oninitialize(e) {
    const r = e.params.protocolVersion;
    return this._clientCapabilities = e.params.capabilities, this._clientVersion = e.params.clientInfo, {
      protocolVersion: bd.includes(r) ? r : mn,
      capabilities: this.getCapabilities(),
      serverInfo: this._serverInfo,
      ...this._instructions && { instructions: this._instructions }
    };
  }
  /**
   * After initialization has completed, this will be populated with the client's reported capabilities.
   */
  getClientCapabilities() {
    return this._clientCapabilities;
  }
  /**
   * After initialization has completed, this will be populated with information about the client's name and version.
   */
  getClientVersion() {
    return this._clientVersion;
  }
  getCapabilities() {
    return this._capabilities;
  }
  async ping() {
    return this.request({ method: "ping" }, Xt);
  }
  // Implementation
  async createMessage(e, r) {
    if ((e.tools || e.toolChoice) && !this._clientCapabilities?.sampling?.tools)
      throw new Error("Client does not support sampling tools capability.");
    if (e.messages.length > 0) {
      const n = e.messages[e.messages.length - 1], s = Array.isArray(n.content) ? n.content : [n.content], i = s.some((u) => u.type === "tool_result"), o = e.messages.length > 1 ? e.messages[e.messages.length - 2] : void 0, a = o ? Array.isArray(o.content) ? o.content : [o.content] : [], c = a.some((u) => u.type === "tool_use");
      if (i) {
        if (s.some((u) => u.type !== "tool_result"))
          throw new Error("The last message must contain only tool_result content if any is present");
        if (!c)
          throw new Error("tool_result blocks are not matching any tool_use from the previous message");
      }
      if (c) {
        const u = new Set(a.filter((h) => h.type === "tool_use").map((h) => h.id)), l = new Set(s.filter((h) => h.type === "tool_result").map((h) => h.toolUseId));
        if (u.size !== l.size || ![...u].every((h) => l.has(h)))
          throw new Error("ids of tool_result blocks and tool_use blocks from previous message do not match");
      }
    }
    return e.tools ? this.request({ method: "sampling/createMessage", params: e }, aa, r) : this.request({ method: "sampling/createMessage", params: e }, Xs, r);
  }
  /**
   * Creates an elicitation request for the given parameters.
   * For backwards compatibility, `mode` may be omitted for form requests and will default to `'form'`.
   * @param params The parameters for the elicitation request.
   * @param options Optional request options.
   * @returns The result of the elicitation request.
   */
  async elicitInput(e, r) {
    switch (e.mode ?? "form") {
      case "url": {
        if (!this._clientCapabilities?.elicitation?.url)
          throw new Error("Client does not support url elicitation.");
        const s = e;
        return this.request({ method: "elicitation/create", params: s }, rn, r);
      }
      case "form": {
        if (!this._clientCapabilities?.elicitation?.form)
          throw new Error("Client does not support form elicitation.");
        const s = e.mode === "form" ? e : { ...e, mode: "form" }, i = await this.request({ method: "elicitation/create", params: s }, rn, r);
        if (i.action === "accept" && i.content && s.requestedSchema)
          try {
            const a = this._jsonSchemaValidator.getValidator(s.requestedSchema)(i.content);
            if (!a.valid)
              throw new G(K.InvalidParams, `Elicitation response content does not match requested schema: ${a.errorMessage}`);
          } catch (o) {
            throw o instanceof G ? o : new G(K.InternalError, `Error validating elicitation response: ${o instanceof Error ? o.message : String(o)}`);
          }
        return i;
      }
    }
  }
  /**
   * Creates a reusable callback that, when invoked, will send a `notifications/elicitation/complete`
   * notification for the specified elicitation ID.
   *
   * @param elicitationId The ID of the elicitation to mark as complete.
   * @param options Optional notification options. Useful when the completion notification should be related to a prior request.
   * @returns A function that emits the completion notification when awaited.
   */
  createElicitationCompletionNotifier(e, r) {
    if (!this._clientCapabilities?.elicitation?.url)
      throw new Error("Client does not support URL elicitation (required for notifications/elicitation/complete)");
    return () => this.notification({
      method: "notifications/elicitation/complete",
      params: {
        elicitationId: e
      }
    }, r);
  }
  async listRoots(e, r) {
    return this.request({ method: "roots/list", params: e }, Wd, r);
  }
  /**
   * Sends a logging message to the client, if connected.
   * Note: You only need to send the parameters object, not the entire JSON RPC message
   * @see LoggingMessageNotification
   * @param params
   * @param sessionId optional for stateless and backward compatibility
   */
  async sendLoggingMessage(e, r) {
    if (this._capabilities.logging && !this.isMessageIgnored(e.level, r))
      return this.notification({ method: "notifications/message", params: e });
  }
  async sendResourceUpdated(e) {
    return this.notification({
      method: "notifications/resources/updated",
      params: e
    });
  }
  async sendResourceListChanged() {
    return this.notification({
      method: "notifications/resources/list_changed"
    });
  }
  async sendToolListChanged() {
    return this.notification({ method: "notifications/tools/list_changed" });
  }
  async sendPromptListChanged() {
    return this.notification({ method: "notifications/prompts/list_changed" });
  }
}
const Bh = /* @__PURE__ */ Symbol.for("mcp.completable");
function nl(t) {
  return !!t && typeof t == "object" && Bh in t;
}
function Q0(t) {
  return t[Bh]?.complete;
}
var sl;
(function(t) {
  t.Completable = "McpCompletable";
})(sl || (sl = {}));
const Y0 = /^[A-Za-z0-9._-]{1,128}$/;
function X0(t) {
  const e = [];
  if (t.length === 0)
    return {
      isValid: !1,
      warnings: ["Tool name cannot be empty"]
    };
  if (t.length > 128)
    return {
      isValid: !1,
      warnings: [`Tool name exceeds maximum length of 128 characters (current: ${t.length})`]
    };
  if (t.includes(" ") && e.push("Tool name contains spaces, which may cause parsing issues"), t.includes(",") && e.push("Tool name contains commas, which may cause parsing issues"), (t.startsWith("-") || t.endsWith("-")) && e.push("Tool name starts or ends with a dash, which may cause parsing issues in some contexts"), (t.startsWith(".") || t.endsWith(".")) && e.push("Tool name starts or ends with a dot, which may cause parsing issues in some contexts"), !Y0.test(t)) {
    const r = t.split("").filter((n) => !/[A-Za-z0-9._-]/.test(n)).filter((n, s, i) => i.indexOf(n) === s);
    return e.push(`Tool name contains invalid characters: ${r.map((n) => `"${n}"`).join(", ")}`, "Allowed characters are: A-Z, a-z, 0-9, underscore (_), dash (-), and dot (.)"), {
      isValid: !1,
      warnings: e
    };
  }
  return {
    isValid: !0,
    warnings: e
  };
}
function eS(t, e) {
  if (e.length > 0) {
    console.warn(`Tool name validation warning for "${t}":`);
    for (const r of e)
      console.warn(`  - ${r}`);
    console.warn("Tool registration will proceed, but this may cause compatibility issues."), console.warn("Consider updating the tool name to conform to the MCP tool naming standard."), console.warn("See SEP: Specify Format for Tool Names (https://github.com/modelcontextprotocol/modelcontextprotocol/issues/986) for more details.");
  }
}
function il(t) {
  const e = X0(t);
  return eS(t, e.warnings), e.isValid;
}
class tS {
  constructor(e) {
    this._mcpServer = e;
  }
  registerToolTask(e, r, n) {
    const s = { taskSupport: "required", ...r.execution };
    if (s.taskSupport === "forbidden")
      throw new Error(`Cannot register task-based tool '${e}' with taskSupport 'forbidden'. Use registerTool() instead.`);
    return this._mcpServer._createRegisteredTool(e, r.title, r.description, r.inputSchema, r.outputSchema, r.annotations, s, r._meta, n);
  }
}
class rS {
  constructor(e, r) {
    this._registeredResources = {}, this._registeredResourceTemplates = {}, this._registeredTools = {}, this._registeredPrompts = {}, this._toolHandlersInitialized = !1, this._completionHandlerInitialized = !1, this._resourceHandlersInitialized = !1, this._promptHandlersInitialized = !1, this.server = new K0(e, r);
  }
  /**
   * Access experimental features.
   *
   * WARNING: These APIs are experimental and may change without notice.
   *
   * @experimental
   */
  get experimental() {
    return this._experimental || (this._experimental = {
      tasks: new tS(this)
    }), this._experimental;
  }
  /**
   * Attaches to the given transport, starts it, and starts listening for messages.
   *
   * The `server` object assumes ownership of the Transport, replacing any callbacks that have already been set, and expects that it is the only user of the Transport instance going forward.
   */
  async connect(e) {
    return await this.server.connect(e);
  }
  /**
   * Closes the connection.
   */
  async close() {
    await this.server.close();
  }
  setToolRequestHandlers() {
    this._toolHandlersInitialized || (this.server.assertCanSetRequestHandler(jt(Ts)), this.server.assertCanSetRequestHandler(jt(tn)), this.server.registerCapabilities({
      tools: {
        listChanged: !0
      }
    }), this.server.setRequestHandler(Ts, () => ({
      tools: Object.entries(this._registeredTools).filter(([, e]) => e.enabled).map(([e, r]) => {
        const n = {
          name: e,
          title: r.title,
          description: r.description,
          inputSchema: (() => {
            const s = Lr(r.inputSchema);
            return s ? Mc(s, {
              strictUnions: !0,
              pipeStrategy: "input"
            }) : nS;
          })(),
          annotations: r.annotations,
          execution: r.execution,
          _meta: r._meta
        };
        if (r.outputSchema) {
          const s = Lr(r.outputSchema);
          s && (n.outputSchema = Mc(s, {
            strictUnions: !0,
            pipeStrategy: "output"
          }));
        }
        return n;
      })
    })), this.server.setRequestHandler(tn, async (e, r) => {
      try {
        const n = this._registeredTools[e.params.name];
        if (!n)
          throw new G(K.InvalidParams, `Tool ${e.params.name} not found`);
        if (!n.enabled)
          throw new G(K.InvalidParams, `Tool ${e.params.name} disabled`);
        const s = !!e.params.task, i = n.execution?.taskSupport, o = "createTask" in n.handler;
        if ((i === "required" || i === "optional") && !o)
          throw new G(K.InternalError, `Tool ${e.params.name} has taskSupport '${i}' but was not registered with registerToolTask`);
        if (i === "required" && !s)
          throw new G(K.MethodNotFound, `Tool ${e.params.name} requires task augmentation (taskSupport: 'required')`);
        if (i === "optional" && !s && o)
          return await this.handleAutomaticTaskPolling(n, e, r);
        const a = await this.validateToolInput(n, e.params.arguments, e.params.name), c = await this.executeToolHandler(n, a, r);
        return s || await this.validateToolOutput(n, c, e.params.name), c;
      } catch (n) {
        if (n instanceof G && n.code === K.UrlElicitationRequired)
          throw n;
        return this.createToolError(n instanceof Error ? n.message : String(n));
      }
    }), this._toolHandlersInitialized = !0);
  }
  /**
   * Creates a tool error result.
   *
   * @param errorMessage - The error message.
   * @returns The tool error result.
   */
  createToolError(e) {
    return {
      content: [
        {
          type: "text",
          text: e
        }
      ],
      isError: !0
    };
  }
  /**
   * Validates tool input arguments against the tool's input schema.
   */
  async validateToolInput(e, r, n) {
    if (!e.inputSchema)
      return;
    const i = Lr(e.inputSchema) ?? e.inputSchema, o = await bi(i, r);
    if (!o.success) {
      const a = "error" in o ? o.error : "Unknown error", c = Si(a);
      throw new G(K.InvalidParams, `Input validation error: Invalid arguments for tool ${n}: ${c}`);
    }
    return o.data;
  }
  /**
   * Validates tool output against the tool's output schema.
   */
  async validateToolOutput(e, r, n) {
    if (!e.outputSchema || !("content" in r) || r.isError)
      return;
    if (!r.structuredContent)
      throw new G(K.InvalidParams, `Output validation error: Tool ${n} has an output schema but no structured content was provided`);
    const s = Lr(e.outputSchema), i = await bi(s, r.structuredContent);
    if (!i.success) {
      const o = "error" in i ? i.error : "Unknown error", a = Si(o);
      throw new G(K.InvalidParams, `Output validation error: Invalid structured content for tool ${n}: ${a}`);
    }
  }
  /**
   * Executes a tool handler (either regular or task-based).
   */
  async executeToolHandler(e, r, n) {
    const s = e.handler;
    if ("createTask" in s) {
      if (!n.taskStore)
        throw new Error("No task store provided.");
      const o = { ...n, taskStore: n.taskStore };
      if (e.inputSchema) {
        const a = s;
        return await Promise.resolve(a.createTask(r, o));
      } else {
        const a = s;
        return await Promise.resolve(a.createTask(o));
      }
    }
    if (e.inputSchema) {
      const o = s;
      return await Promise.resolve(o(r, n));
    } else {
      const o = s;
      return await Promise.resolve(o(n));
    }
  }
  /**
   * Handles automatic task polling for tools with taskSupport 'optional'.
   */
  async handleAutomaticTaskPolling(e, r, n) {
    if (!n.taskStore)
      throw new Error("No task store provided for task-capable tool.");
    const s = await this.validateToolInput(e, r.params.arguments, r.params.name), i = e.handler, o = { ...n, taskStore: n.taskStore }, a = s ? await Promise.resolve(i.createTask(s, o)) : (
      // eslint-disable-next-line @typescript-eslint/no-explicit-any
      await Promise.resolve(i.createTask(o))
    ), c = a.task.taskId;
    let u = a.task;
    const l = u.pollInterval ?? 5e3;
    for (; u.status !== "completed" && u.status !== "failed" && u.status !== "cancelled"; ) {
      await new Promise((p) => setTimeout(p, l));
      const h = await n.taskStore.getTask(c);
      if (!h)
        throw new G(K.InternalError, `Task ${c} not found during polling`);
      u = h;
    }
    return await n.taskStore.getTaskResult(c);
  }
  setCompletionRequestHandler() {
    this._completionHandlerInitialized || (this.server.assertCanSetRequestHandler(jt(Yi)), this.server.registerCapabilities({
      completions: {}
    }), this.server.setRequestHandler(Yi, async (e) => {
      switch (e.params.ref.type) {
        case "ref/prompt":
          return iw(e), this.handlePromptCompletion(e, e.params.ref);
        case "ref/resource":
          return ow(e), this.handleResourceCompletion(e, e.params.ref);
        default:
          throw new G(K.InvalidParams, `Invalid completion reference: ${e.params.ref}`);
      }
    }), this._completionHandlerInitialized = !0);
  }
  async handlePromptCompletion(e, r) {
    const n = this._registeredPrompts[r.name];
    if (!n)
      throw new G(K.InvalidParams, `Prompt ${r.name} not found`);
    if (!n.enabled)
      throw new G(K.InvalidParams, `Prompt ${r.name} disabled`);
    if (!n.argsSchema)
      return Vr;
    const i = Mr(n.argsSchema)?.[e.params.argument.name];
    if (!nl(i))
      return Vr;
    const o = Q0(i);
    if (!o)
      return Vr;
    const a = await o(e.params.argument.value, e.params.context);
    return al(a);
  }
  async handleResourceCompletion(e, r) {
    const n = Object.values(this._registeredResourceTemplates).find((o) => o.resourceTemplate.uriTemplate.toString() === r.uri);
    if (!n) {
      if (this._registeredResources[r.uri])
        return Vr;
      throw new G(K.InvalidParams, `Resource template ${e.params.ref.uri} not found`);
    }
    const s = n.resourceTemplate.completeCallback(e.params.argument.name);
    if (!s)
      return Vr;
    const i = await s(e.params.argument.value, e.params.context);
    return al(i);
  }
  setResourceRequestHandlers() {
    this._resourceHandlersInitialized || (this.server.assertCanSetRequestHandler(jt(Wi)), this.server.assertCanSetRequestHandler(jt(Gi)), this.server.assertCanSetRequestHandler(jt(Ji)), this.server.registerCapabilities({
      resources: {
        listChanged: !0
      }
    }), this.server.setRequestHandler(Wi, async (e, r) => {
      const n = Object.entries(this._registeredResources).filter(([i, o]) => o.enabled).map(([i, o]) => ({
        uri: i,
        name: o.name,
        ...o.metadata
      })), s = [];
      for (const i of Object.values(this._registeredResourceTemplates)) {
        if (!i.resourceTemplate.listCallback)
          continue;
        const o = await i.resourceTemplate.listCallback(r);
        for (const a of o.resources)
          s.push({
            ...i.metadata,
            // the defined resource metadata should override the template metadata if present
            ...a
          });
      }
      return { resources: [...n, ...s] };
    }), this.server.setRequestHandler(Gi, async () => ({ resourceTemplates: Object.entries(this._registeredResourceTemplates).map(([r, n]) => ({
      name: r,
      uriTemplate: n.resourceTemplate.uriTemplate.toString(),
      ...n.metadata
    })) })), this.server.setRequestHandler(Ji, async (e, r) => {
      const n = new URL(e.params.uri), s = this._registeredResources[n.toString()];
      if (s) {
        if (!s.enabled)
          throw new G(K.InvalidParams, `Resource ${n} disabled`);
        return s.readCallback(n, r);
      }
      for (const i of Object.values(this._registeredResourceTemplates)) {
        const o = i.resourceTemplate.uriTemplate.match(n.toString());
        if (o)
          return i.readCallback(n, o, r);
      }
      throw new G(K.InvalidParams, `Resource ${n} not found`);
    }), this._resourceHandlersInitialized = !0);
  }
  setPromptRequestHandlers() {
    this._promptHandlersInitialized || (this.server.assertCanSetRequestHandler(jt(Ki)), this.server.assertCanSetRequestHandler(jt(Qi)), this.server.registerCapabilities({
      prompts: {
        listChanged: !0
      }
    }), this.server.setRequestHandler(Ki, () => ({
      prompts: Object.entries(this._registeredPrompts).filter(([, e]) => e.enabled).map(([e, r]) => ({
        name: e,
        title: r.title,
        description: r.description,
        arguments: r.argsSchema ? sS(r.argsSchema) : void 0
      }))
    })), this.server.setRequestHandler(Qi, async (e, r) => {
      const n = this._registeredPrompts[e.params.name];
      if (!n)
        throw new G(K.InvalidParams, `Prompt ${e.params.name} not found`);
      if (!n.enabled)
        throw new G(K.InvalidParams, `Prompt ${e.params.name} disabled`);
      if (n.argsSchema) {
        const s = Lr(n.argsSchema), i = await bi(s, e.params.arguments);
        if (!i.success) {
          const c = "error" in i ? i.error : "Unknown error", u = Si(c);
          throw new G(K.InvalidParams, `Invalid arguments for prompt ${e.params.name}: ${u}`);
        }
        const o = i.data, a = n.callback;
        return await Promise.resolve(a(o, r));
      } else {
        const s = n.callback;
        return await Promise.resolve(s(r));
      }
    }), this._promptHandlersInitialized = !0);
  }
  resource(e, r, ...n) {
    let s;
    typeof n[0] == "object" && (s = n.shift());
    const i = n[0];
    if (typeof r == "string") {
      if (this._registeredResources[r])
        throw new Error(`Resource ${r} is already registered`);
      const o = this._createRegisteredResource(e, void 0, r, s, i);
      return this.setResourceRequestHandlers(), this.sendResourceListChanged(), o;
    } else {
      if (this._registeredResourceTemplates[e])
        throw new Error(`Resource template ${e} is already registered`);
      const o = this._createRegisteredResourceTemplate(e, void 0, r, s, i);
      return this.setResourceRequestHandlers(), this.sendResourceListChanged(), o;
    }
  }
  registerResource(e, r, n, s) {
    if (typeof r == "string") {
      if (this._registeredResources[r])
        throw new Error(`Resource ${r} is already registered`);
      const i = this._createRegisteredResource(e, n.title, r, n, s);
      return this.setResourceRequestHandlers(), this.sendResourceListChanged(), i;
    } else {
      if (this._registeredResourceTemplates[e])
        throw new Error(`Resource template ${e} is already registered`);
      const i = this._createRegisteredResourceTemplate(e, n.title, r, n, s);
      return this.setResourceRequestHandlers(), this.sendResourceListChanged(), i;
    }
  }
  _createRegisteredResource(e, r, n, s, i) {
    const o = {
      name: e,
      title: r,
      metadata: s,
      readCallback: i,
      enabled: !0,
      disable: () => o.update({ enabled: !1 }),
      enable: () => o.update({ enabled: !0 }),
      remove: () => o.update({ uri: null }),
      update: (a) => {
        typeof a.uri < "u" && a.uri !== n && (delete this._registeredResources[n], a.uri && (this._registeredResources[a.uri] = o)), typeof a.name < "u" && (o.name = a.name), typeof a.title < "u" && (o.title = a.title), typeof a.metadata < "u" && (o.metadata = a.metadata), typeof a.callback < "u" && (o.readCallback = a.callback), typeof a.enabled < "u" && (o.enabled = a.enabled), this.sendResourceListChanged();
      }
    };
    return this._registeredResources[n] = o, o;
  }
  _createRegisteredResourceTemplate(e, r, n, s, i) {
    const o = {
      resourceTemplate: n,
      title: r,
      metadata: s,
      readCallback: i,
      enabled: !0,
      disable: () => o.update({ enabled: !1 }),
      enable: () => o.update({ enabled: !0 }),
      remove: () => o.update({ name: null }),
      update: (u) => {
        typeof u.name < "u" && u.name !== e && (delete this._registeredResourceTemplates[e], u.name && (this._registeredResourceTemplates[u.name] = o)), typeof u.title < "u" && (o.title = u.title), typeof u.template < "u" && (o.resourceTemplate = u.template), typeof u.metadata < "u" && (o.metadata = u.metadata), typeof u.callback < "u" && (o.readCallback = u.callback), typeof u.enabled < "u" && (o.enabled = u.enabled), this.sendResourceListChanged();
      }
    };
    this._registeredResourceTemplates[e] = o;
    const a = n.uriTemplate.variableNames;
    return Array.isArray(a) && a.some((u) => !!n.completeCallback(u)) && this.setCompletionRequestHandler(), o;
  }
  _createRegisteredPrompt(e, r, n, s, i) {
    const o = {
      title: r,
      description: n,
      argsSchema: s === void 0 ? void 0 : vr(s),
      callback: i,
      enabled: !0,
      disable: () => o.update({ enabled: !1 }),
      enable: () => o.update({ enabled: !0 }),
      remove: () => o.update({ name: null }),
      update: (a) => {
        typeof a.name < "u" && a.name !== e && (delete this._registeredPrompts[e], a.name && (this._registeredPrompts[a.name] = o)), typeof a.title < "u" && (o.title = a.title), typeof a.description < "u" && (o.description = a.description), typeof a.argsSchema < "u" && (o.argsSchema = vr(a.argsSchema)), typeof a.callback < "u" && (o.callback = a.callback), typeof a.enabled < "u" && (o.enabled = a.enabled), this.sendPromptListChanged();
      }
    };
    return this._registeredPrompts[e] = o, s && Object.values(s).some((c) => {
      const u = c instanceof Lo ? c._def?.innerType : c;
      return nl(u);
    }) && this.setCompletionRequestHandler(), o;
  }
  _createRegisteredTool(e, r, n, s, i, o, a, c, u) {
    il(e);
    const l = {
      title: r,
      description: n,
      inputSchema: ol(s),
      outputSchema: ol(i),
      annotations: o,
      execution: a,
      _meta: c,
      handler: u,
      enabled: !0,
      disable: () => l.update({ enabled: !1 }),
      enable: () => l.update({ enabled: !0 }),
      remove: () => l.update({ name: null }),
      update: (h) => {
        typeof h.name < "u" && h.name !== e && (typeof h.name == "string" && il(h.name), delete this._registeredTools[e], h.name && (this._registeredTools[h.name] = l)), typeof h.title < "u" && (l.title = h.title), typeof h.description < "u" && (l.description = h.description), typeof h.paramsSchema < "u" && (l.inputSchema = vr(h.paramsSchema)), typeof h.outputSchema < "u" && (l.outputSchema = vr(h.outputSchema)), typeof h.callback < "u" && (l.handler = h.callback), typeof h.annotations < "u" && (l.annotations = h.annotations), typeof h._meta < "u" && (l._meta = h._meta), typeof h.enabled < "u" && (l.enabled = h.enabled), this.sendToolListChanged();
      }
    };
    return this._registeredTools[e] = l, this.setToolRequestHandlers(), this.sendToolListChanged(), l;
  }
  /**
   * tool() implementation. Parses arguments passed to overrides defined above.
   */
  tool(e, ...r) {
    if (this._registeredTools[e])
      throw new Error(`Tool ${e} is already registered`);
    let n, s, i, o;
    if (typeof r[0] == "string" && (n = r.shift()), r.length > 1) {
      const c = r[0];
      if (_o(c))
        s = r.shift(), r.length > 1 && typeof r[0] == "object" && r[0] !== null && !_o(r[0]) && (o = r.shift());
      else if (typeof c == "object" && c !== null) {
        if (Object.values(c).some((u) => typeof u == "object" && u !== null))
          throw new Error(`Tool ${e} expected a Zod schema or ToolAnnotations, but received an unrecognized object`);
        o = r.shift();
      }
    }
    const a = r[0];
    return this._createRegisteredTool(e, void 0, n, s, i, o, { taskSupport: "forbidden" }, void 0, a);
  }
  /**
   * Registers a tool with a config object and callback.
   */
  registerTool(e, r, n) {
    if (this._registeredTools[e])
      throw new Error(`Tool ${e} is already registered`);
    const { title: s, description: i, inputSchema: o, outputSchema: a, annotations: c, _meta: u } = r;
    return this._createRegisteredTool(e, s, i, o, a, c, { taskSupport: "forbidden" }, u, n);
  }
  prompt(e, ...r) {
    if (this._registeredPrompts[e])
      throw new Error(`Prompt ${e} is already registered`);
    let n;
    typeof r[0] == "string" && (n = r.shift());
    let s;
    r.length > 1 && (s = r.shift());
    const i = r[0], o = this._createRegisteredPrompt(e, void 0, n, s, i);
    return this.setPromptRequestHandlers(), this.sendPromptListChanged(), o;
  }
  /**
   * Registers a prompt with a config object and callback.
   */
  registerPrompt(e, r, n) {
    if (this._registeredPrompts[e])
      throw new Error(`Prompt ${e} is already registered`);
    const { title: s, description: i, argsSchema: o } = r, a = this._createRegisteredPrompt(e, s, i, o, n);
    return this.setPromptRequestHandlers(), this.sendPromptListChanged(), a;
  }
  /**
   * Checks if the server is connected to a transport.
   * @returns True if the server is connected
   */
  isConnected() {
    return this.server.transport !== void 0;
  }
  /**
   * Sends a logging message to the client, if connected.
   * Note: You only need to send the parameters object, not the entire JSON RPC message
   * @see LoggingMessageNotification
   * @param params
   * @param sessionId optional for stateless and backward compatibility
   */
  async sendLoggingMessage(e, r) {
    return this.server.sendLoggingMessage(e, r);
  }
  /**
   * Sends a resource list changed event to the client, if connected.
   */
  sendResourceListChanged() {
    this.isConnected() && this.server.sendResourceListChanged();
  }
  /**
   * Sends a tool list changed event to the client, if connected.
   */
  sendToolListChanged() {
    this.isConnected() && this.server.sendToolListChanged();
  }
  /**
   * Sends a prompt list changed event to the client, if connected.
   */
  sendPromptListChanged() {
    this.isConnected() && this.server.sendPromptListChanged();
  }
}
const nS = {
  type: "object",
  properties: {}
};
function Wh(t) {
  return t !== null && typeof t == "object" && "parse" in t && typeof t.parse == "function" && "safeParse" in t && typeof t.safeParse == "function";
}
function Gh(t) {
  return "_def" in t || "_zod" in t || Wh(t);
}
function _o(t) {
  return typeof t != "object" || t === null || Gh(t) ? !1 : Object.keys(t).length === 0 ? !0 : Object.values(t).some(Wh);
}
function ol(t) {
  if (t) {
    if (_o(t))
      return vr(t);
    if (!Gh(t))
      throw new Error("inputSchema must be a Zod schema or raw shape, received an unrecognized object");
    return t;
  }
}
function sS(t) {
  const e = Mr(t);
  return e ? Object.entries(e).map(([r, n]) => {
    const s = Jv(n), i = Kv(n);
    return {
      name: r,
      description: s,
      required: !i
    };
  }) : [];
}
function jt(t) {
  const r = Mr(t)?.method;
  if (!r)
    throw new Error("Schema is missing a method literal");
  const n = ri(r);
  if (typeof n == "string")
    return n;
  throw new Error("Schema method literal must be a string");
}
function al(t) {
  return {
    completion: {
      values: t.slice(0, 100),
      total: t.length,
      hasMore: t.length > 100
    }
  };
}
const Vr = {
  completion: {
    values: [],
    hasMore: !1
  }
};
class iS {
  constructor(e) {
    this._client = e;
  }
  /**
   * Calls a tool and returns an AsyncGenerator that yields response messages.
   * The generator is guaranteed to end with either a 'result' or 'error' message.
   *
   * This method provides streaming access to tool execution, allowing you to
   * observe intermediate task status updates for long-running tool calls.
   * Automatically validates structured output if the tool has an outputSchema.
   *
   * @example
   * ```typescript
   * const stream = client.experimental.tasks.callToolStream({ name: 'myTool', arguments: {} });
   * for await (const message of stream) {
   *   switch (message.type) {
   *     case 'taskCreated':
   *       console.log('Tool execution started:', message.task.taskId);
   *       break;
   *     case 'taskStatus':
   *       console.log('Tool status:', message.task.status);
   *       break;
   *     case 'result':
   *       console.log('Tool result:', message.result);
   *       break;
   *     case 'error':
   *       console.error('Tool error:', message.error);
   *       break;
   *   }
   * }
   * ```
   *
   * @param params - Tool call parameters (name and arguments)
   * @param resultSchema - Zod schema for validating the result (defaults to CallToolResultSchema)
   * @param options - Optional request options (timeout, signal, task creation params, etc.)
   * @returns AsyncGenerator that yields ResponseMessage objects
   *
   * @experimental
   */
  async *callToolStream(e, r = Sn, n) {
    const s = this._client, i = {
      ...n,
      // We check if the tool is known to be a task during auto-configuration, but assume
      // the caller knows what they're doing if they pass this explicitly
      task: n?.task ?? (s.isToolTask(e.name) ? {} : void 0)
    }, o = s.requestStream({ method: "tools/call", params: e }, r, i), a = s.getToolOutputValidator(e.name);
    for await (const c of o) {
      if (c.type === "result" && a) {
        const u = c.result;
        if (!u.structuredContent && !u.isError) {
          yield {
            type: "error",
            error: new G(K.InvalidRequest, `Tool ${e.name} has an output schema but did not return structured content`)
          };
          return;
        }
        if (u.structuredContent)
          try {
            const l = a(u.structuredContent);
            if (!l.valid) {
              yield {
                type: "error",
                error: new G(K.InvalidParams, `Structured content does not match the tool's output schema: ${l.errorMessage}`)
              };
              return;
            }
          } catch (l) {
            if (l instanceof G) {
              yield { type: "error", error: l };
              return;
            }
            yield {
              type: "error",
              error: new G(K.InvalidParams, `Failed to validate structured content: ${l instanceof Error ? l.message : String(l)}`)
            };
            return;
          }
      }
      yield c;
    }
  }
  /**
   * Gets the current status of a task.
   *
   * @param taskId - The task identifier
   * @param options - Optional request options
   * @returns The task status
   *
   * @experimental
   */
  async getTask(e, r) {
    return this._client.getTask({ taskId: e }, r);
  }
  /**
   * Retrieves the result of a completed task.
   *
   * @param taskId - The task identifier
   * @param resultSchema - Zod schema for validating the result
   * @param options - Optional request options
   * @returns The task result
   *
   * @experimental
   */
  async getTaskResult(e, r, n) {
    return this._client.getTaskResult({ taskId: e }, r, n);
  }
  /**
   * Lists tasks with optional pagination.
   *
   * @param cursor - Optional pagination cursor
   * @param options - Optional request options
   * @returns List of tasks with optional next cursor
   *
   * @experimental
   */
  async listTasks(e, r) {
    return this._client.listTasks(e ? { cursor: e } : void 0, r);
  }
  /**
   * Cancels a running task.
   *
   * @param taskId - The task identifier
   * @param options - Optional request options
   *
   * @experimental
   */
  async cancelTask(e, r) {
    return this._client.cancelTask({ taskId: e }, r);
  }
  /**
   * Sends a request and returns an AsyncGenerator that yields response messages.
   * The generator is guaranteed to end with either a 'result' or 'error' message.
   *
   * This method provides streaming access to request processing, allowing you to
   * observe intermediate task status updates for task-augmented requests.
   *
   * @param request - The request to send
   * @param resultSchema - Zod schema for validating the result
   * @param options - Optional request options (timeout, signal, task creation params, etc.)
   * @returns AsyncGenerator that yields ResponseMessage objects
   *
   * @experimental
   */
  requestStream(e, r, n) {
    return this._client.requestStream(e, r, n);
  }
}
function ys(t, e) {
  if (!(!t || e === null || typeof e != "object")) {
    if (t.type === "object" && t.properties && typeof t.properties == "object") {
      const r = e, n = t.properties;
      for (const s of Object.keys(n)) {
        const i = n[s];
        r[s] === void 0 && Object.prototype.hasOwnProperty.call(i, "default") && (r[s] = i.default), r[s] !== void 0 && ys(i, r[s]);
      }
    }
    if (Array.isArray(t.anyOf))
      for (const r of t.anyOf)
        typeof r != "boolean" && ys(r, e);
    if (Array.isArray(t.oneOf))
      for (const r of t.oneOf)
        typeof r != "boolean" && ys(r, e);
  }
}
function oS(t) {
  if (!t)
    return { supportsFormMode: !1, supportsUrlMode: !1 };
  const e = t.form !== void 0, r = t.url !== void 0;
  return { supportsFormMode: e || !e && !r, supportsUrlMode: r };
}
class aS extends Nh {
  /**
   * Initializes this client with the given name and version information.
   */
  constructor(e, r) {
    super(r), this._clientInfo = e, this._cachedToolOutputValidators = /* @__PURE__ */ new Map(), this._cachedKnownTaskTools = /* @__PURE__ */ new Set(), this._cachedRequiredTaskTools = /* @__PURE__ */ new Set(), this._listChangedDebounceTimers = /* @__PURE__ */ new Map(), this._capabilities = r?.capabilities ?? {}, this._jsonSchemaValidator = r?.jsonSchemaValidator ?? new Hh(), r?.listChanged && (this._pendingListChangedConfig = r.listChanged);
  }
  /**
   * Set up handlers for list changed notifications based on config and server capabilities.
   * This should only be called after initialization when server capabilities are known.
   * Handlers are silently skipped if the server doesn't advertise the corresponding listChanged capability.
   * @internal
   */
  _setupListChangedHandlers(e) {
    e.tools && this._serverCapabilities?.tools?.listChanged && this._setupListChangedHandler("tools", Zd, e.tools, async () => (await this.listTools()).tools), e.prompts && this._serverCapabilities?.prompts?.listChanged && this._setupListChangedHandler("prompts", Ud, e.prompts, async () => (await this.listPrompts()).prompts), e.resources && this._serverCapabilities?.resources?.listChanged && this._setupListChangedHandler("resources", jd, e.resources, async () => (await this.listResources()).resources);
  }
  /**
   * Access experimental features.
   *
   * WARNING: These APIs are experimental and may change without notice.
   *
   * @experimental
   */
  get experimental() {
    return this._experimental || (this._experimental = {
      tasks: new iS(this)
    }), this._experimental;
  }
  /**
   * Registers new capabilities. This can only be called before connecting to a transport.
   *
   * The new capabilities will be merged with any existing capabilities previously given (e.g., at initialization).
   */
  registerCapabilities(e) {
    if (this.transport)
      throw new Error("Cannot register capabilities after connecting to transport");
    this._capabilities = xh(this._capabilities, e);
  }
  /**
   * Override request handler registration to enforce client-side validation for elicitation.
   */
  setRequestHandler(e, r) {
    const s = Mr(e)?.method;
    if (!s)
      throw new Error("Schema is missing a method literal");
    const i = ri(s);
    if (typeof i != "string")
      throw new Error("Schema method literal must be a string");
    const o = i;
    if (o === "elicitation/create") {
      const a = async (c, u) => {
        const l = _t(Vd, c);
        if (!l.success) {
          const m = l.error instanceof Error ? l.error.message : String(l.error);
          throw new G(K.InvalidParams, `Invalid elicitation request: ${m}`);
        }
        const { params: h } = l.data;
        h.mode = h.mode ?? "form";
        const { supportsFormMode: p, supportsUrlMode: g } = oS(this._capabilities.elicitation);
        if (h.mode === "form" && !p)
          throw new G(K.InvalidParams, "Client does not support form-mode elicitation requests");
        if (h.mode === "url" && !g)
          throw new G(K.InvalidParams, "Client does not support URL-mode elicitation requests");
        const S = await Promise.resolve(r(c, u));
        if (h.task) {
          const m = _t(Er, S);
          if (!m.success) {
            const w = m.error instanceof Error ? m.error.message : String(m.error);
            throw new G(K.InvalidParams, `Invalid task creation result: ${w}`);
          }
          return m.data;
        }
        const k = _t(rn, S);
        if (!k.success) {
          const m = k.error instanceof Error ? k.error.message : String(k.error);
          throw new G(K.InvalidParams, `Invalid elicitation result: ${m}`);
        }
        const y = k.data, b = h.mode === "form" ? h.requestedSchema : void 0;
        if (h.mode === "form" && y.action === "accept" && y.content && b && this._capabilities.elicitation?.form?.applyDefaults)
          try {
            ys(b, y.content);
          } catch {
          }
        return y;
      };
      return super.setRequestHandler(e, a);
    }
    if (o === "sampling/createMessage") {
      const a = async (c, u) => {
        const l = _t(Fd, c);
        if (!l.success) {
          const y = l.error instanceof Error ? l.error.message : String(l.error);
          throw new G(K.InvalidParams, `Invalid sampling request: ${y}`);
        }
        const { params: h } = l.data, p = await Promise.resolve(r(c, u));
        if (h.task) {
          const y = _t(Er, p);
          if (!y.success) {
            const b = y.error instanceof Error ? y.error.message : String(y.error);
            throw new G(K.InvalidParams, `Invalid task creation result: ${b}`);
          }
          return y.data;
        }
        const S = h.tools || h.toolChoice ? aa : Xs, k = _t(S, p);
        if (!k.success) {
          const y = k.error instanceof Error ? k.error.message : String(k.error);
          throw new G(K.InvalidParams, `Invalid sampling result: ${y}`);
        }
        return k.data;
      };
      return super.setRequestHandler(e, a);
    }
    return super.setRequestHandler(e, r);
  }
  assertCapability(e, r) {
    if (!this._serverCapabilities?.[e])
      throw new Error(`Server does not support ${e} (required for ${r})`);
  }
  async connect(e, r) {
    if (await super.connect(e), e.sessionId === void 0)
      try {
        const n = await this.request({
          method: "initialize",
          params: {
            protocolVersion: mn,
            capabilities: this._capabilities,
            clientInfo: this._clientInfo
          }
        }, Id, r);
        if (n === void 0)
          throw new Error(`Server sent invalid initialize result: ${n}`);
        if (!bd.includes(n.protocolVersion))
          throw new Error(`Server's protocol version is not supported: ${n.protocolVersion}`);
        this._serverCapabilities = n.capabilities, this._serverVersion = n.serverInfo, e.setProtocolVersion && e.setProtocolVersion(n.protocolVersion), this._instructions = n.instructions, await this.notification({
          method: "notifications/initialized"
        }), this._pendingListChangedConfig && (this._setupListChangedHandlers(this._pendingListChangedConfig), this._pendingListChangedConfig = void 0);
      } catch (n) {
        throw this.close(), n;
      }
  }
  /**
   * After initialization has completed, this will be populated with the server's reported capabilities.
   */
  getServerCapabilities() {
    return this._serverCapabilities;
  }
  /**
   * After initialization has completed, this will be populated with information about the server's name and version.
   */
  getServerVersion() {
    return this._serverVersion;
  }
  /**
   * After initialization has completed, this may be populated with information about the server's instructions.
   */
  getInstructions() {
    return this._instructions;
  }
  assertCapabilityForMethod(e) {
    switch (e) {
      case "logging/setLevel":
        if (!this._serverCapabilities?.logging)
          throw new Error(`Server does not support logging (required for ${e})`);
        break;
      case "prompts/get":
      case "prompts/list":
        if (!this._serverCapabilities?.prompts)
          throw new Error(`Server does not support prompts (required for ${e})`);
        break;
      case "resources/list":
      case "resources/templates/list":
      case "resources/read":
      case "resources/subscribe":
      case "resources/unsubscribe":
        if (!this._serverCapabilities?.resources)
          throw new Error(`Server does not support resources (required for ${e})`);
        if (e === "resources/subscribe" && !this._serverCapabilities.resources.subscribe)
          throw new Error(`Server does not support resource subscriptions (required for ${e})`);
        break;
      case "tools/call":
      case "tools/list":
        if (!this._serverCapabilities?.tools)
          throw new Error(`Server does not support tools (required for ${e})`);
        break;
      case "completion/complete":
        if (!this._serverCapabilities?.completions)
          throw new Error(`Server does not support completions (required for ${e})`);
        break;
    }
  }
  assertNotificationCapability(e) {
    switch (e) {
      case "notifications/roots/list_changed":
        if (!this._capabilities.roots?.listChanged)
          throw new Error(`Client does not support roots list changed notifications (required for ${e})`);
        break;
    }
  }
  assertRequestHandlerCapability(e) {
    if (this._capabilities)
      switch (e) {
        case "sampling/createMessage":
          if (!this._capabilities.sampling)
            throw new Error(`Client does not support sampling capability (required for ${e})`);
          break;
        case "elicitation/create":
          if (!this._capabilities.elicitation)
            throw new Error(`Client does not support elicitation capability (required for ${e})`);
          break;
        case "roots/list":
          if (!this._capabilities.roots)
            throw new Error(`Client does not support roots capability (required for ${e})`);
          break;
        case "tasks/get":
        case "tasks/list":
        case "tasks/result":
        case "tasks/cancel":
          if (!this._capabilities.tasks)
            throw new Error(`Client does not support tasks capability (required for ${e})`);
          break;
      }
  }
  assertTaskCapability(e) {
    Fh(this._serverCapabilities?.tasks?.requests, e, "Server");
  }
  assertTaskHandlerCapability(e) {
    this._capabilities && Vh(this._capabilities.tasks?.requests, e, "Client");
  }
  async ping(e) {
    return this.request({ method: "ping" }, Xt, e);
  }
  async complete(e, r) {
    return this.request({ method: "completion/complete", params: e }, Bd, r);
  }
  async setLoggingLevel(e, r) {
    return this.request({ method: "logging/setLevel", params: { level: e } }, Xt, r);
  }
  async getPrompt(e, r) {
    return this.request({ method: "prompts/get", params: e }, qd, r);
  }
  async listPrompts(e, r) {
    return this.request({ method: "prompts/list", params: e }, Md, r);
  }
  async listResources(e, r) {
    return this.request({ method: "resources/list", params: e }, Nd, r);
  }
  async listResourceTemplates(e, r) {
    return this.request({ method: "resources/templates/list", params: e }, xd, r);
  }
  async readResource(e, r) {
    return this.request({ method: "resources/read", params: e }, zd, r);
  }
  async subscribeResource(e, r) {
    return this.request({ method: "resources/subscribe", params: e }, Xt, r);
  }
  async unsubscribeResource(e, r) {
    return this.request({ method: "resources/unsubscribe", params: e }, Xt, r);
  }
  /**
   * Calls a tool and waits for the result. Automatically validates structured output if the tool has an outputSchema.
   *
   * For task-based execution with streaming behavior, use client.experimental.tasks.callToolStream() instead.
   */
  async callTool(e, r = Sn, n) {
    if (this.isToolTaskRequired(e.name))
      throw new G(K.InvalidRequest, `Tool "${e.name}" requires task-based execution. Use client.experimental.tasks.callToolStream() instead.`);
    const s = await this.request({ method: "tools/call", params: e }, r, n), i = this.getToolOutputValidator(e.name);
    if (i) {
      if (!s.structuredContent && !s.isError)
        throw new G(K.InvalidRequest, `Tool ${e.name} has an output schema but did not return structured content`);
      if (s.structuredContent)
        try {
          const o = i(s.structuredContent);
          if (!o.valid)
            throw new G(K.InvalidParams, `Structured content does not match the tool's output schema: ${o.errorMessage}`);
        } catch (o) {
          throw o instanceof G ? o : new G(K.InvalidParams, `Failed to validate structured content: ${o instanceof Error ? o.message : String(o)}`);
        }
    }
    return s;
  }
  isToolTask(e) {
    return this._serverCapabilities?.tasks?.requests?.tools?.call ? this._cachedKnownTaskTools.has(e) : !1;
  }
  /**
   * Check if a tool requires task-based execution.
   * Unlike isToolTask which includes 'optional' tools, this only checks for 'required'.
   */
  isToolTaskRequired(e) {
    return this._cachedRequiredTaskTools.has(e);
  }
  /**
   * Cache validators for tool output schemas.
   * Called after listTools() to pre-compile validators for better performance.
   */
  cacheToolMetadata(e) {
    this._cachedToolOutputValidators.clear(), this._cachedKnownTaskTools.clear(), this._cachedRequiredTaskTools.clear();
    for (const r of e) {
      if (r.outputSchema) {
        const s = this._jsonSchemaValidator.getValidator(r.outputSchema);
        this._cachedToolOutputValidators.set(r.name, s);
      }
      const n = r.execution?.taskSupport;
      (n === "required" || n === "optional") && this._cachedKnownTaskTools.add(r.name), n === "required" && this._cachedRequiredTaskTools.add(r.name);
    }
  }
  /**
   * Get cached validator for a tool
   */
  getToolOutputValidator(e) {
    return this._cachedToolOutputValidators.get(e);
  }
  async listTools(e, r) {
    const n = await this.request({ method: "tools/list", params: e }, Ld, r);
    return this.cacheToolMetadata(n.tools), n;
  }
  /**
   * Set up a single list changed handler.
   * @internal
   */
  _setupListChangedHandler(e, r, n, s) {
    const i = Iy.safeParse(n);
    if (!i.success)
      throw new Error(`Invalid ${e} listChanged options: ${i.error.message}`);
    if (typeof n.onChanged != "function")
      throw new Error(`Invalid ${e} listChanged options: onChanged must be a function`);
    const { autoRefresh: o, debounceMs: a } = i.data, { onChanged: c } = n, u = async () => {
      if (!o) {
        c(null, null);
        return;
      }
      try {
        const h = await s();
        c(null, h);
      } catch (h) {
        const p = h instanceof Error ? h : new Error(String(h));
        c(p, null);
      }
    }, l = () => {
      if (a) {
        const h = this._listChangedDebounceTimers.get(e);
        h && clearTimeout(h);
        const p = setTimeout(u, a);
        this._listChangedDebounceTimers.set(e, p);
      } else
        u();
    };
    this.setNotificationHandler(r, l);
  }
  async sendRootsListChanged() {
    return this.notification({ method: "notifications/roots/list_changed" });
  }
}
var fs = {};
var cl;
function cS() {
  if (cl) return fs;
  cl = 1;
  var t = /; *([!#$%&'*+.^_`|~0-9A-Za-z-]+) *= *("(?:[\u000b\u0020\u0021\u0023-\u005b\u005d-\u007e\u0080-\u00ff]|\\[\u000b\u0020-\u00ff])*"|[!#$%&'*+.^_`|~0-9A-Za-z-]+) */g, e = /^[\u000b\u0020-\u007e\u0080-\u00ff]+$/, r = /^[!#$%&'*+.^_`|~0-9A-Za-z-]+$/, n = /\\([\u000b\u0020-\u00ff])/g, s = /([\\"])/g, i = /^[!#$%&'*+.^_`|~0-9A-Za-z-]+\/[!#$%&'*+.^_`|~0-9A-Za-z-]+$/;
  fs.format = o, fs.parse = a;
  function o(h) {
    if (!h || typeof h != "object")
      throw new TypeError("argument obj is required");
    var p = h.parameters, g = h.type;
    if (!g || !i.test(g))
      throw new TypeError("invalid type");
    var S = g;
    if (p && typeof p == "object")
      for (var k, y = Object.keys(p).sort(), b = 0; b < y.length; b++) {
        if (k = y[b], !r.test(k))
          throw new TypeError("invalid parameter name");
        S += "; " + k + "=" + u(p[k]);
      }
    return S;
  }
  function a(h) {
    if (!h)
      throw new TypeError("argument string is required");
    var p = typeof h == "object" ? c(h) : h;
    if (typeof p != "string")
      throw new TypeError("argument string is required to be a string");
    var g = p.indexOf(";"), S = g !== -1 ? p.slice(0, g).trim() : p.trim();
    if (!i.test(S))
      throw new TypeError("invalid media type");
    var k = new l(S.toLowerCase());
    if (g !== -1) {
      var y, b, m;
      for (t.lastIndex = g; b = t.exec(p); ) {
        if (b.index !== g)
          throw new TypeError("invalid parameter format");
        g += b[0].length, y = b[1].toLowerCase(), m = b[2], m.charCodeAt(0) === 34 && (m = m.slice(1, -1), m.indexOf("\\") !== -1 && (m = m.replace(n, "$1"))), k.parameters[y] = m;
      }
      if (g !== p.length)
        throw new TypeError("invalid parameter format");
    }
    return k;
  }
  function c(h) {
    var p;
    if (typeof h.getHeader == "function" ? p = h.getHeader("content-type") : typeof h.headers == "object" && (p = h.headers && h.headers["content-type"]), typeof p != "string")
      throw new TypeError("content-type header is missing from object");
    return p;
  }
  function u(h) {
    var p = String(h);
    if (r.test(p))
      return p;
    if (p.length > 0 && !e.test(p))
      throw new TypeError("invalid parameter value");
    return '"' + p.replace(s, "\\$1") + '"';
  }
  function l(h) {
    this.parameters = /* @__PURE__ */ Object.create(null), this.type = h;
  }
  return fs;
}
var uS = cS();
const lS = /* @__PURE__ */ ga(uS);
function dS(t) {
  if (t)
    try {
      return lS.parse(t).type;
    } catch {
      const e = (t.split(";", 1)[0] ?? "").trim().toLowerCase();
      return e === "" || t.slice(e.length).includes(",") ? void 0 : e;
    }
}
function yo(t) {
  return t ? t instanceof Headers ? Object.fromEntries(t.entries()) : Array.isArray(t) ? Object.fromEntries(t) : { ...t } : {};
}
function hS(t = fetch, e) {
  return e ? async (r, n) => {
    const s = {
      ...e,
      ...n,
      // Headers need special handling - merge instead of replace
      headers: n?.headers ? { ...yo(e.headers), ...yo(n.headers) } : e.headers
    };
    return t(r, s);
  } : t;
}
let va;
va = globalThis.crypto;
async function fS(t) {
  return (await va).getRandomValues(new Uint8Array(t));
}
async function pS(t) {
  const e = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789-._~", r = Math.pow(2, 8) - Math.pow(2, 8) % e.length;
  let n = "";
  for (; n.length < t; ) {
    const s = await fS(t - n.length);
    for (const i of s)
      i < r && (n += e[i % e.length]);
  }
  return n;
}
async function mS(t) {
  return await pS(t);
}
async function gS(t) {
  const e = await (await va).subtle.digest("SHA-256", new TextEncoder().encode(t));
  return btoa(String.fromCharCode(...new Uint8Array(e))).replace(/\//g, "_").replace(/\+/g, "-").replace(/=/g, "");
}
async function _S(t) {
  if (t || (t = 43), t < 43 || t > 128)
    throw `Expected a length between 43 and 128. Received ${t}.`;
  const e = await mS(t), r = await gS(e);
  return {
    code_verifier: e,
    code_challenge: r
  };
}
const Ve = Jg().superRefine((t, e) => {
  if (!URL.canParse(t))
    return e.addIssue({
      code: L_.custom,
      message: "URL must be parseable",
      fatal: !0
    }), nf;
}).refine((t) => {
  const e = new URL(t);
  return e.protocol !== "javascript:" && e.protocol !== "data:" && e.protocol !== "vbscript:";
}, { message: "URL cannot use javascript:, data:, or vbscript: scheme" }), yS = Be({
  resource: T().url(),
  authorization_servers: B(Ve).optional(),
  jwks_uri: T().url().optional(),
  scopes_supported: B(T()).optional(),
  bearer_methods_supported: B(T()).optional(),
  resource_signing_alg_values_supported: B(T()).optional(),
  resource_name: T().optional(),
  resource_documentation: T().optional(),
  resource_policy_uri: T().url().optional(),
  resource_tos_uri: T().url().optional(),
  tls_client_certificate_bound_access_tokens: Pe().optional(),
  authorization_details_types_supported: B(T()).optional(),
  dpop_signing_alg_values_supported: B(T()).optional(),
  dpop_bound_access_tokens_required: Pe().optional()
}), Jh = Be({
  issuer: T(),
  authorization_endpoint: Ve,
  token_endpoint: Ve,
  registration_endpoint: Ve.optional(),
  scopes_supported: B(T()).optional(),
  response_types_supported: B(T()),
  response_modes_supported: B(T()).optional(),
  grant_types_supported: B(T()).optional(),
  token_endpoint_auth_methods_supported: B(T()).optional(),
  token_endpoint_auth_signing_alg_values_supported: B(T()).optional(),
  service_documentation: Ve.optional(),
  revocation_endpoint: Ve.optional(),
  revocation_endpoint_auth_methods_supported: B(T()).optional(),
  revocation_endpoint_auth_signing_alg_values_supported: B(T()).optional(),
  introspection_endpoint: T().optional(),
  introspection_endpoint_auth_methods_supported: B(T()).optional(),
  introspection_endpoint_auth_signing_alg_values_supported: B(T()).optional(),
  code_challenge_methods_supported: B(T()).optional(),
  client_id_metadata_document_supported: Pe().optional()
}), wS = Be({
  issuer: T(),
  authorization_endpoint: Ve,
  token_endpoint: Ve,
  userinfo_endpoint: Ve.optional(),
  jwks_uri: Ve,
  registration_endpoint: Ve.optional(),
  scopes_supported: B(T()).optional(),
  response_types_supported: B(T()),
  response_modes_supported: B(T()).optional(),
  grant_types_supported: B(T()).optional(),
  acr_values_supported: B(T()).optional(),
  subject_types_supported: B(T()),
  id_token_signing_alg_values_supported: B(T()),
  id_token_encryption_alg_values_supported: B(T()).optional(),
  id_token_encryption_enc_values_supported: B(T()).optional(),
  userinfo_signing_alg_values_supported: B(T()).optional(),
  userinfo_encryption_alg_values_supported: B(T()).optional(),
  userinfo_encryption_enc_values_supported: B(T()).optional(),
  request_object_signing_alg_values_supported: B(T()).optional(),
  request_object_encryption_alg_values_supported: B(T()).optional(),
  request_object_encryption_enc_values_supported: B(T()).optional(),
  token_endpoint_auth_methods_supported: B(T()).optional(),
  token_endpoint_auth_signing_alg_values_supported: B(T()).optional(),
  display_values_supported: B(T()).optional(),
  claim_types_supported: B(T()).optional(),
  claims_supported: B(T()).optional(),
  service_documentation: T().optional(),
  claims_locales_supported: B(T()).optional(),
  ui_locales_supported: B(T()).optional(),
  claims_parameter_supported: Pe().optional(),
  request_parameter_supported: Pe().optional(),
  request_uri_parameter_supported: Pe().optional(),
  require_request_uri_registration: Pe().optional(),
  op_policy_uri: Ve.optional(),
  op_tos_uri: Ve.optional(),
  client_id_metadata_document_supported: Pe().optional()
}), vS = W({
  ...wS.shape,
  ...Jh.pick({
    code_challenge_methods_supported: !0
  }).shape
}), bS = W({
  access_token: T(),
  id_token: T().optional(),
  // Optional for OAuth 2.1, but necessary in OpenID Connect
  token_type: T(),
  expires_in: Z_().optional(),
  scope: T().optional(),
  refresh_token: T().optional()
}).strip(), SS = W({
  error: T(),
  error_description: T().optional(),
  error_uri: T().optional()
}), ul = Ve.optional().or(te("").transform(() => {
})), kS = W({
  redirect_uris: B(Ve),
  token_endpoint_auth_method: T().optional(),
  grant_types: B(T()).optional(),
  response_types: B(T()).optional(),
  client_name: T().optional(),
  client_uri: Ve.optional(),
  logo_uri: ul,
  scope: T().optional(),
  contacts: B(T()).optional(),
  tos_uri: ul,
  policy_uri: T().optional(),
  jwks_uri: Ve.optional(),
  jwks: g_().optional(),
  software_id: T().optional(),
  software_version: T().optional(),
  software_statement: T().optional()
}).strip(), $S = W({
  client_id: T(),
  client_secret: T().optional(),
  client_id_issued_at: ve().optional(),
  client_secret_expires_at: ve().optional()
}).strip(), ES = kS.merge($S);
W({
  error: T(),
  error_description: T().optional()
}).strip();
W({
  token: T(),
  token_type_hint: T().optional()
}).strip();
function TS(t) {
  const e = typeof t == "string" ? new URL(t) : new URL(t.href);
  return e.hash = "", e;
}
function RS({ requestedResource: t, configuredResource: e }) {
  const r = typeof t == "string" ? new URL(t) : new URL(t.href), n = typeof e == "string" ? new URL(e) : new URL(e.href);
  if (r.origin !== n.origin || r.pathname.length < n.pathname.length)
    return !1;
  const s = r.pathname.endsWith("/") ? r.pathname : r.pathname + "/", i = n.pathname.endsWith("/") ? n.pathname : n.pathname + "/";
  return s.startsWith(i);
}
class Le extends Error {
  constructor(e, r) {
    super(e), this.errorUri = r, this.name = this.constructor.name;
  }
  /**
   * Converts the error to a standard OAuth error response object
   */
  toResponseObject() {
    const e = {
      error: this.errorCode,
      error_description: this.message
    };
    return this.errorUri && (e.error_uri = this.errorUri), e;
  }
  get errorCode() {
    return this.constructor.errorCode;
  }
}
class wo extends Le {
}
wo.errorCode = "invalid_request";
class Ds extends Le {
}
Ds.errorCode = "invalid_client";
class Ls extends Le {
}
Ls.errorCode = "invalid_grant";
class Zs extends Le {
}
Zs.errorCode = "unauthorized_client";
class vo extends Le {
}
vo.errorCode = "unsupported_grant_type";
class bo extends Le {
}
bo.errorCode = "invalid_scope";
class So extends Le {
}
So.errorCode = "access_denied";
class xr extends Le {
}
xr.errorCode = "server_error";
class ko extends Le {
}
ko.errorCode = "temporarily_unavailable";
class $o extends Le {
}
$o.errorCode = "unsupported_response_type";
class Eo extends Le {
}
Eo.errorCode = "unsupported_token_type";
class To extends Le {
}
To.errorCode = "invalid_token";
class Ro extends Le {
}
Ro.errorCode = "method_not_allowed";
class Io extends Le {
}
Io.errorCode = "too_many_requests";
class Hs extends Le {
}
Hs.errorCode = "invalid_client_metadata";
class Po extends Le {
}
Po.errorCode = "insufficient_scope";
class Co extends Le {
}
Co.errorCode = "invalid_target";
const IS = {
  [wo.errorCode]: wo,
  [Ds.errorCode]: Ds,
  [Ls.errorCode]: Ls,
  [Zs.errorCode]: Zs,
  [vo.errorCode]: vo,
  [bo.errorCode]: bo,
  [So.errorCode]: So,
  [xr.errorCode]: xr,
  [ko.errorCode]: ko,
  [$o.errorCode]: $o,
  [Eo.errorCode]: Eo,
  [To.errorCode]: To,
  [Ro.errorCode]: Ro,
  [Io.errorCode]: Io,
  [Hs.errorCode]: Hs,
  [Po.errorCode]: Po,
  [Co.errorCode]: Co
};
class lr extends Error {
  constructor(e) {
    super(e ?? "Unauthorized");
  }
}
function PS(t) {
  return ["client_secret_basic", "client_secret_post", "none"].includes(t);
}
const Mi = "code", qi = "S256";
function CS(t, e) {
  const r = t.client_secret !== void 0;
  return "token_endpoint_auth_method" in t && t.token_endpoint_auth_method && PS(t.token_endpoint_auth_method) && (e.length === 0 || e.includes(t.token_endpoint_auth_method)) ? t.token_endpoint_auth_method : e.length === 0 ? r ? "client_secret_basic" : "none" : r && e.includes("client_secret_basic") ? "client_secret_basic" : r && e.includes("client_secret_post") ? "client_secret_post" : e.includes("none") ? "none" : r ? "client_secret_post" : "none";
}
function OS(t, e, r, n) {
  const { client_id: s, client_secret: i } = e;
  switch (t) {
    case "client_secret_basic":
      AS(s, i, r);
      return;
    case "client_secret_post":
      NS(s, i, n);
      return;
    case "none":
      xS(s, n);
      return;
    default:
      throw new Error(`Unsupported client authentication method: ${t}`);
  }
}
function AS(t, e, r) {
  if (!e)
    throw new Error("client_secret_basic authentication requires a client_secret");
  const n = btoa(`${t}:${e}`);
  r.set("Authorization", `Basic ${n}`);
}
function NS(t, e, r) {
  r.set("client_id", t), e && r.set("client_secret", e);
}
function xS(t, e) {
  e.set("client_id", t);
}
async function Kh(t) {
  const e = t instanceof Response ? t.status : void 0, r = t instanceof Response ? await t.text() : t;
  try {
    const n = SS.parse(JSON.parse(r)), { error: s, error_description: i, error_uri: o } = n, a = IS[s] || xr;
    return new a(i || "", o);
  } catch (n) {
    const s = `${e ? `HTTP ${e}: ` : ""}Invalid OAuth error response: ${n}. Raw body: ${r}`;
    return new xr(s);
  }
}
async function ps(t, e) {
  try {
    return await Ui(t, e);
  } catch (r) {
    if (r instanceof Ds || r instanceof Zs)
      return await t.invalidateCredentials?.("all"), await Ui(t, e);
    if (r instanceof Ls)
      return await t.invalidateCredentials?.("tokens"), await Ui(t, e);
    throw r;
  }
}
async function Ui(t, { serverUrl: e, authorizationCode: r, scope: n, resourceMetadataUrl: s, fetchFn: i }) {
  const o = await t.discoveryState?.();
  let a, c, u, l = s;
  if (!l && o?.resourceMetadataUrl && (l = new URL(o.resourceMetadataUrl)), o?.authorizationServerUrl) {
    if (c = o.authorizationServerUrl, a = o.resourceMetadata, u = o.authorizationServerMetadata ?? await Yh(c, { fetchFn: i }), !a)
      try {
        a = await Qh(e, { resourceMetadataUrl: l }, i);
      } catch {
      }
    (u !== o.authorizationServerMetadata || a !== o.resourceMetadata) && await t.saveDiscoveryState?.({
      authorizationServerUrl: String(c),
      resourceMetadataUrl: l?.toString(),
      resourceMetadata: a,
      authorizationServerMetadata: u
    });
  } else {
    const w = await LS(e, { resourceMetadataUrl: l, fetchFn: i });
    c = w.authorizationServerUrl, u = w.authorizationServerMetadata, a = w.resourceMetadata, await t.saveDiscoveryState?.({
      authorizationServerUrl: String(c),
      resourceMetadataUrl: l?.toString(),
      resourceMetadata: a,
      authorizationServerMetadata: u
    });
  }
  const h = await jS(e, t, a), p = n || a?.scopes_supported?.join(" ") || t.clientMetadata.scope;
  let g = await Promise.resolve(t.clientInformation());
  if (!g) {
    if (r !== void 0)
      throw new Error("Existing OAuth client information is required when exchanging an authorization code");
    const w = u?.client_id_metadata_document_supported === !0, $ = t.clientMetadataUrl;
    if ($ && !zS($))
      throw new Hs(`clientMetadataUrl must be a valid HTTPS URL with a non-root pathname, got: ${$}`);
    if (w && $)
      g = {
        client_id: $
      }, await t.saveClientInformation?.(g);
    else {
      if (!t.saveClientInformation)
        throw new Error("OAuth client information must be saveable for dynamic registration");
      const f = await BS(c, {
        metadata: u,
        clientMetadata: t.clientMetadata,
        scope: p,
        fetchFn: i
      });
      await t.saveClientInformation(f), g = f;
    }
  }
  const S = !t.redirectUrl;
  if (r !== void 0 || S) {
    const w = await VS(t, c, {
      metadata: u,
      resource: h,
      authorizationCode: r,
      fetchFn: i
    });
    return await t.saveTokens(w), "AUTHORIZED";
  }
  const k = await t.tokens();
  if (k?.refresh_token)
    try {
      const w = await FS(c, {
        metadata: u,
        clientInformation: g,
        refreshToken: k.refresh_token,
        resource: h,
        addClientAuthentication: t.addClientAuthentication,
        fetchFn: i
      });
      return await t.saveTokens(w), "AUTHORIZED";
    } catch (w) {
      if (!(!(w instanceof Le) || w instanceof xr)) throw w;
    }
  const y = t.state ? await t.state() : void 0, { authorizationUrl: b, codeVerifier: m } = await ZS(c, {
    metadata: u,
    clientInformation: g,
    state: y,
    redirectUrl: t.redirectUrl,
    scope: p,
    resource: h
  });
  return await t.saveCodeVerifier(m), await t.redirectToAuthorization(b), "REDIRECT";
}
function zS(t) {
  if (!t)
    return !1;
  try {
    const e = new URL(t);
    return e.protocol === "https:" && e.pathname !== "/";
  } catch {
    return !1;
  }
}
async function jS(t, e, r) {
  const n = TS(t);
  if (e.validateResourceURL)
    return await e.validateResourceURL(n, r?.resource);
  if (r) {
    if (!RS({ requestedResource: n, configuredResource: r.resource }))
      throw new Error(`Protected resource ${r.resource} does not match expected ${n} (or origin)`);
    return new URL(r.resource);
  }
}
function ll(t) {
  const e = t.headers.get("WWW-Authenticate");
  if (!e)
    return {};
  const [r, n] = e.split(" ");
  if (r.toLowerCase() !== "bearer" || !n)
    return {};
  const s = Di(t, "resource_metadata") || void 0;
  let i;
  if (s)
    try {
      i = new URL(s);
    } catch {
    }
  const o = Di(t, "scope") || void 0, a = Di(t, "error") || void 0;
  return {
    resourceMetadataUrl: i,
    scope: o,
    error: a
  };
}
function Di(t, e) {
  const r = t.headers.get("WWW-Authenticate");
  if (!r)
    return null;
  const n = new RegExp(`${e}=(?:"([^"]+)"|([^\\s,]+))`), s = r.match(n);
  return s ? s[1] || s[2] : null;
}
async function Qh(t, e, r = fetch) {
  const n = await US(t, "oauth-protected-resource", r, {
    protocolVersion: e?.protocolVersion,
    metadataUrl: e?.resourceMetadataUrl
  });
  if (!n || n.status === 404)
    throw await n?.body?.cancel(), new Error("Resource server does not implement OAuth 2.0 Protected Resource Metadata.");
  if (!n.ok)
    throw await n.body?.cancel(), new Error(`HTTP ${n.status} trying to load well-known OAuth protected resource metadata.`);
  return yS.parse(await n.json());
}
async function ba(t, e, r = fetch) {
  try {
    return await r(t, { headers: e });
  } catch (n) {
    if (n instanceof TypeError)
      return e ? ba(t, void 0, r) : void 0;
    throw n;
  }
}
function MS(t, e = "", r = {}) {
  return e.endsWith("/") && (e = e.slice(0, -1)), r.prependPathname ? `${e}/.well-known/${t}` : `/.well-known/${t}${e}`;
}
async function dl(t, e, r = fetch) {
  return await ba(t, {
    "MCP-Protocol-Version": e
  }, r);
}
function qS(t, e) {
  return !t || t.status >= 400 && t.status < 500 && e !== "/";
}
async function US(t, e, r, n) {
  const s = new URL(t), i = n?.protocolVersion ?? mn;
  let o;
  if (n?.metadataUrl)
    o = new URL(n.metadataUrl);
  else {
    const c = MS(e, s.pathname);
    o = new URL(c, n?.metadataServerUrl ?? s), o.search = s.search;
  }
  let a = await dl(o, i, r);
  if (!n?.metadataUrl && qS(a, s.pathname)) {
    const c = new URL(`/.well-known/${e}`, s);
    a = await dl(c, i, r);
  }
  return a;
}
function DS(t) {
  const e = typeof t == "string" ? new URL(t) : t, r = e.pathname !== "/", n = [];
  if (!r)
    return n.push({
      url: new URL("/.well-known/oauth-authorization-server", e.origin),
      type: "oauth"
    }), n.push({
      url: new URL("/.well-known/openid-configuration", e.origin),
      type: "oidc"
    }), n;
  let s = e.pathname;
  return s.endsWith("/") && (s = s.slice(0, -1)), n.push({
    url: new URL(`/.well-known/oauth-authorization-server${s}`, e.origin),
    type: "oauth"
  }), n.push({
    url: new URL(`/.well-known/openid-configuration${s}`, e.origin),
    type: "oidc"
  }), n.push({
    url: new URL(`${s}/.well-known/openid-configuration`, e.origin),
    type: "oidc"
  }), n;
}
async function Yh(t, { fetchFn: e = fetch, protocolVersion: r = mn } = {}) {
  const n = {
    "MCP-Protocol-Version": r,
    Accept: "application/json"
  }, s = DS(t);
  for (const { url: i, type: o } of s) {
    const a = await ba(i, n, e);
    if (a) {
      if (!a.ok) {
        if (await a.body?.cancel(), a.status >= 400 && a.status < 500)
          continue;
        throw new Error(`HTTP ${a.status} trying to load ${o === "oauth" ? "OAuth" : "OpenID provider"} metadata from ${i}`);
      }
      return o === "oauth" ? Jh.parse(await a.json()) : vS.parse(await a.json());
    }
  }
}
async function LS(t, e) {
  let r, n;
  try {
    r = await Qh(t, { resourceMetadataUrl: e?.resourceMetadataUrl }, e?.fetchFn), r.authorization_servers && r.authorization_servers.length > 0 && (n = r.authorization_servers[0]);
  } catch {
  }
  n || (n = String(new URL("/", t)));
  const s = await Yh(n, { fetchFn: e?.fetchFn });
  return {
    authorizationServerUrl: n,
    authorizationServerMetadata: s,
    resourceMetadata: r
  };
}
async function ZS(t, { metadata: e, clientInformation: r, redirectUrl: n, scope: s, state: i, resource: o }) {
  let a;
  if (e) {
    if (a = new URL(e.authorization_endpoint), !e.response_types_supported.includes(Mi))
      throw new Error(`Incompatible auth server: does not support response type ${Mi}`);
    if (e.code_challenge_methods_supported && !e.code_challenge_methods_supported.includes(qi))
      throw new Error(`Incompatible auth server: does not support code challenge method ${qi}`);
  } else
    a = new URL("/authorize", t);
  const c = await _S(), u = c.code_verifier, l = c.code_challenge;
  return a.searchParams.set("response_type", Mi), a.searchParams.set("client_id", r.client_id), a.searchParams.set("code_challenge", l), a.searchParams.set("code_challenge_method", qi), a.searchParams.set("redirect_uri", String(n)), i && a.searchParams.set("state", i), s && a.searchParams.set("scope", s), s?.includes("offline_access") && a.searchParams.append("prompt", "consent"), o && a.searchParams.set("resource", o.href), { authorizationUrl: a, codeVerifier: u };
}
function HS(t, e, r) {
  return new URLSearchParams({
    grant_type: "authorization_code",
    code: t,
    code_verifier: e,
    redirect_uri: String(r)
  });
}
async function Xh(t, { metadata: e, tokenRequestParams: r, clientInformation: n, addClientAuthentication: s, resource: i, fetchFn: o }) {
  const a = e?.token_endpoint ? new URL(e.token_endpoint) : new URL("/token", t), c = new Headers({
    "Content-Type": "application/x-www-form-urlencoded",
    Accept: "application/json"
  });
  if (i && r.set("resource", i.href), s)
    await s(c, r, a, e);
  else if (n) {
    const l = e?.token_endpoint_auth_methods_supported ?? [], h = CS(n, l);
    OS(h, n, c, r);
  }
  const u = await (o ?? fetch)(a, {
    method: "POST",
    headers: c,
    body: r
  });
  if (!u.ok)
    throw await Kh(u);
  return bS.parse(await u.json());
}
async function FS(t, { metadata: e, clientInformation: r, refreshToken: n, resource: s, addClientAuthentication: i, fetchFn: o }) {
  const a = new URLSearchParams({
    grant_type: "refresh_token",
    refresh_token: n
  }), c = await Xh(t, {
    metadata: e,
    tokenRequestParams: a,
    clientInformation: r,
    addClientAuthentication: i,
    resource: s,
    fetchFn: o
  });
  return { refresh_token: n, ...c };
}
async function VS(t, e, { metadata: r, resource: n, authorizationCode: s, fetchFn: i } = {}) {
  const o = t.clientMetadata.scope;
  let a;
  if (t.prepareTokenRequest && (a = await t.prepareTokenRequest(o)), !a) {
    if (!s)
      throw new Error("Either provider.prepareTokenRequest() or authorizationCode is required");
    if (!t.redirectUrl)
      throw new Error("redirectUrl is required for authorization_code flow");
    const u = await t.codeVerifier();
    a = HS(s, u, t.redirectUrl);
  }
  const c = await t.clientInformation();
  return Xh(e, {
    metadata: r,
    tokenRequestParams: a,
    clientInformation: c ?? void 0,
    addClientAuthentication: t.addClientAuthentication,
    resource: n,
    fetchFn: i
  });
}
async function BS(t, { metadata: e, clientMetadata: r, scope: n, fetchFn: s }) {
  let i;
  if (e) {
    if (!e.registration_endpoint)
      throw new Error("Incompatible auth server: does not support dynamic client registration");
    i = new URL(e.registration_endpoint);
  } else
    i = new URL("/register", t);
  const o = await (s ?? fetch)(i, {
    method: "POST",
    headers: {
      "Content-Type": "application/json"
    },
    body: JSON.stringify({
      ...r,
      ...n !== void 0 ? { scope: n } : {}
    })
  });
  if (!o.ok)
    throw await Kh(o);
  return ES.parse(await o.json());
}
class Li extends Error {
  constructor(e, r) {
    super(e), this.name = "ParseError", this.type = r.type, this.field = r.field, this.value = r.value, this.line = r.line;
  }
}
const hl = 10, WS = 13, Qt = 32;
function Zi(t) {
}
function GS(t) {
  if (typeof t == "function")
    throw new TypeError(
      "`config` must be an object, got a function instead. Did you mean `createParser({onEvent: fn})`?"
    );
  const { onEvent: e = Zi, onError: r = Zi, onRetry: n = Zi, onComment: s, maxBufferSize: i } = t, o = [];
  let a = 0, c = !0, u, l = "", h = 0, p, g = !1;
  function S(d) {
    if (g)
      throw new Error(
        "Cannot feed parser: it was terminated after exceeding the configured max buffer size. Call `reset()` to resume parsing."
      );
    if (c && (c = !1, d.charCodeAt(0) === 239 && d.charCodeAt(1) === 187 && d.charCodeAt(2) === 191 && (d = d.slice(3))), o.length === 0) {
      const E = y(d);
      E !== "" && (o.push(E), a = E.length), k();
      return;
    }
    if (d.indexOf(`
`) === -1 && d.indexOf("\r") === -1) {
      o.push(d), a += d.length, k();
      return;
    }
    o.push(d);
    const f = o.join("");
    o.length = 0, a = 0;
    const _ = y(f);
    _ !== "" && (o.push(_), a = _.length), k();
  }
  function k() {
    i !== void 0 && (a + l.length <= i || (g = !0, o.length = 0, a = 0, u = void 0, l = "", h = 0, p = void 0, r(
      new Li(`Buffered data exceeded max buffer size of ${i} characters`, {
        type: "max-buffer-size-exceeded"
      })
    )));
  }
  function y(d) {
    let f = 0;
    if (d.indexOf("\r") === -1) {
      let _ = d.indexOf(`
`, f);
      for (; _ !== -1; ) {
        if (f === _) {
          h > 0 && e({ id: u, event: p, data: l }), u = void 0, l = "", h = 0, p = void 0, f = _ + 1, _ = d.indexOf(`
`, f);
          continue;
        }
        const E = d.charCodeAt(f);
        if (fl(d, f, E)) {
          const z = d.charCodeAt(f + 5) === Qt ? f + 6 : f + 5, C = d.slice(z, _);
          if (h === 0 && d.charCodeAt(_ + 1) === hl) {
            e({ id: u, event: p, data: C }), u = void 0, l = "", p = void 0, f = _ + 2, _ = d.indexOf(`
`, f);
            continue;
          }
          l = h === 0 ? C : `${l}
${C}`, h++;
        } else pl(d, f, E) ? p = d.slice(
          d.charCodeAt(f + 6) === Qt ? f + 7 : f + 6,
          _
        ) || void 0 : b(d, f, _);
        f = _ + 1, _ = d.indexOf(`
`, f);
      }
      return d.slice(f);
    }
    for (; f < d.length; ) {
      const _ = d.indexOf("\r", f), E = d.indexOf(`
`, f);
      let z = -1;
      if (_ !== -1 && E !== -1 ? z = _ < E ? _ : E : _ !== -1 ? _ === d.length - 1 ? z = -1 : z = _ : E !== -1 && (z = E), z === -1)
        break;
      b(d, f, z), f = z + 1, d.charCodeAt(f - 1) === WS && d.charCodeAt(f) === hl && f++;
    }
    return d.slice(f);
  }
  function b(d, f, _) {
    if (f === _) {
      w();
      return;
    }
    const E = d.charCodeAt(f);
    if (fl(d, f, E)) {
      const q = d.charCodeAt(f + 5) === Qt ? f + 6 : f + 5, se = d.slice(q, _);
      l = h === 0 ? se : `${l}
${se}`, h++;
      return;
    }
    if (pl(d, f, E)) {
      p = d.slice(d.charCodeAt(f + 6) === Qt ? f + 7 : f + 6, _) || void 0;
      return;
    }
    if (E === 105 && d.charCodeAt(f + 1) === 100 && d.charCodeAt(f + 2) === 58) {
      const q = d.slice(d.charCodeAt(f + 3) === Qt ? f + 4 : f + 3, _);
      u = q.includes("\0") ? void 0 : q;
      return;
    }
    if (E === 58) {
      if (s) {
        const q = d.slice(f, _);
        s(q.slice(d.charCodeAt(f + 1) === Qt ? 2 : 1));
      }
      return;
    }
    const z = d.slice(f, _), C = z.indexOf(":");
    if (C === -1) {
      m(z, "", z);
      return;
    }
    const P = z.slice(0, C), j = z.charCodeAt(C + 1) === Qt ? 2 : 1, O = z.slice(C + j);
    m(P, O, z);
  }
  function m(d, f, _) {
    switch (d) {
      case "event":
        p = f || void 0;
        break;
      case "data":
        l = h === 0 ? f : `${l}
${f}`, h++;
        break;
      case "id":
        u = f.includes("\0") ? void 0 : f;
        break;
      case "retry":
        /^\d+$/.test(f) ? n(parseInt(f, 10)) : r(
          new Li(`Invalid \`retry\` value: "${f}"`, {
            type: "invalid-retry",
            value: f,
            line: _
          })
        );
        break;
      default:
        r(
          new Li(
            `Unknown field "${d.length > 20 ? `${d.slice(0, 20)}…` : d}"`,
            { type: "unknown-field", field: d, value: f, line: _ }
          )
        );
        break;
    }
  }
  function w() {
    h > 0 && e({
      id: u,
      event: p,
      data: l
    }), u = void 0, l = "", h = 0, p = void 0;
  }
  function $(d = {}) {
    if (d.consume && o.length > 0) {
      const f = o.join("");
      b(f, 0, f.length);
    }
    c = !0, u = void 0, l = "", h = 0, p = void 0, o.length = 0, a = 0, g = !1;
  }
  return { feed: S, reset: $ };
}
function fl(t, e, r) {
  return r === 100 && t.charCodeAt(e + 1) === 97 && t.charCodeAt(e + 2) === 116 && t.charCodeAt(e + 3) === 97 && t.charCodeAt(e + 4) === 58;
}
function pl(t, e, r) {
  return r === 101 && t.charCodeAt(e + 1) === 118 && t.charCodeAt(e + 2) === 101 && t.charCodeAt(e + 3) === 110 && t.charCodeAt(e + 4) === 116 && t.charCodeAt(e + 5) === 58;
}
class JS extends TransformStream {
  constructor({ onError: e, onRetry: r, onComment: n, maxBufferSize: s } = {}) {
    let i;
    super({
      start(o) {
        i = GS({
          onEvent: (a) => {
            o.enqueue(a);
          },
          onError(a) {
            typeof e == "function" && e(a), (e === "terminate" || a.type === "max-buffer-size-exceeded") && o.error(a);
          },
          onRetry: r,
          onComment: n,
          maxBufferSize: s
        });
      },
      transform(o) {
        i.feed(o);
      }
    });
  }
}
const KS = {
  initialReconnectionDelay: 1e3,
  maxReconnectionDelay: 3e4,
  reconnectionDelayGrowFactor: 1.5,
  maxRetries: 2
};
class dr extends Error {
  constructor(e, r) {
    super(`Streamable HTTP error: ${r}`), this.code = e;
  }
}
class QS {
  constructor(e, r) {
    this._hasCompletedAuthFlow = !1, this._url = e, this._resourceMetadataUrl = void 0, this._scope = void 0, this._requestInit = r?.requestInit, this._authProvider = r?.authProvider, this._fetch = r?.fetch, this._fetchWithInit = hS(r?.fetch, r?.requestInit), this._sessionId = r?.sessionId, this._reconnectionOptions = r?.reconnectionOptions ?? KS;
  }
  async _authThenStart() {
    if (!this._authProvider)
      throw new lr("No auth provider");
    let e;
    try {
      e = await ps(this._authProvider, {
        serverUrl: this._url,
        resourceMetadataUrl: this._resourceMetadataUrl,
        scope: this._scope,
        fetchFn: this._fetchWithInit
      });
    } catch (r) {
      throw this.onerror?.(r), r;
    }
    if (e !== "AUTHORIZED")
      throw new lr();
    return await this._startOrAuthSse({ resumptionToken: void 0 });
  }
  async _commonHeaders() {
    const e = {};
    if (this._authProvider) {
      const n = await this._authProvider.tokens();
      n && (e.Authorization = `Bearer ${n.access_token}`);
    }
    this._sessionId && (e["mcp-session-id"] = this._sessionId), this._protocolVersion && (e["mcp-protocol-version"] = this._protocolVersion);
    const r = yo(this._requestInit?.headers);
    return new Headers({
      ...e,
      ...r
    });
  }
  async _startOrAuthSse(e) {
    const { resumptionToken: r } = e;
    try {
      const n = await this._commonHeaders();
      n.set("Accept", "text/event-stream"), r && n.set("last-event-id", r);
      const s = await (this._fetch ?? fetch)(this._url, {
        method: "GET",
        headers: n,
        signal: this._abortController?.signal
      });
      if (!s.ok) {
        if (await s.body?.cancel(), s.status === 401 && this._authProvider)
          return await this._authThenStart();
        if (s.status === 405)
          return;
        throw new dr(s.status, `Failed to open SSE stream: ${s.statusText}`);
      }
      this._handleSseStream(s.body, e, !0);
    } catch (n) {
      throw this.onerror?.(n), n;
    }
  }
  /**
   * Calculates the next reconnection delay using  backoff algorithm
   *
   * @param attempt Current reconnection attempt count for the specific stream
   * @returns Time to wait in milliseconds before next reconnection attempt
   */
  _getNextReconnectionDelay(e) {
    if (this._serverRetryMs !== void 0)
      return this._serverRetryMs;
    const r = this._reconnectionOptions.initialReconnectionDelay, n = this._reconnectionOptions.reconnectionDelayGrowFactor, s = this._reconnectionOptions.maxReconnectionDelay;
    return Math.min(r * Math.pow(n, e), s);
  }
  /**
   * Schedule a reconnection attempt using server-provided retry interval or backoff
   *
   * @param lastEventId The ID of the last received event for resumability
   * @param attemptCount Current reconnection attempt count for this specific stream
   */
  _scheduleReconnection(e, r = 0) {
    const n = this._reconnectionOptions.maxRetries;
    if (r >= n) {
      this.onerror?.(new Error(`Maximum reconnection attempts (${n}) exceeded.`));
      return;
    }
    const s = this._getNextReconnectionDelay(r);
    this._reconnectionTimeout = setTimeout(() => {
      this._startOrAuthSse(e).catch((i) => {
        this.onerror?.(new Error(`Failed to reconnect SSE stream: ${i instanceof Error ? i.message : String(i)}`)), this._scheduleReconnection(e, r + 1);
      });
    }, s);
  }
  _handleSseStream(e, r, n) {
    if (!e)
      return;
    const { onresumptiontoken: s, replayMessageId: i } = r;
    let o, a = !1, c = !1;
    (async () => {
      try {
        const l = e.pipeThrough(new TextDecoderStream()).pipeThrough(new JS({
          onRetry: (g) => {
            this._serverRetryMs = g;
          }
        })).getReader();
        for (; ; ) {
          const { value: g, done: S } = await l.read();
          if (S)
            break;
          if (g.id && (o = g.id, a = !0, s?.(g.id)), !!g.data && (!g.event || g.event === "message"))
            try {
              const k = ms.parse(JSON.parse(g.data));
              Wr(k) && (c = !0, i !== void 0 && (k.id = i)), this.onmessage?.(k);
            } catch (k) {
              this.onerror?.(k);
            }
        }
        (n || a) && !c && this._abortController && !this._abortController.signal.aborted && this._scheduleReconnection({
          resumptionToken: o,
          onresumptiontoken: s,
          replayMessageId: i
        }, 0);
      } catch (l) {
        if (this.onerror?.(new Error(`SSE stream disconnected: ${l}`)), (n || a) && !c && this._abortController && !this._abortController.signal.aborted)
          try {
            this._scheduleReconnection({
              resumptionToken: o,
              onresumptiontoken: s,
              replayMessageId: i
            }, 0);
          } catch (g) {
            this.onerror?.(new Error(`Failed to reconnect: ${g instanceof Error ? g.message : String(g)}`));
          }
      }
    })();
  }
  async start() {
    if (this._abortController)
      throw new Error("StreamableHTTPClientTransport already started! If using Client class, note that connect() calls start() automatically.");
    this._abortController = new AbortController();
  }
  /**
   * Call this method after the user has finished authorizing via their user agent and is redirected back to the MCP client application. This will exchange the authorization code for an access token, enabling the next connection attempt to successfully auth.
   */
  async finishAuth(e) {
    if (!this._authProvider)
      throw new lr("No auth provider");
    if (await ps(this._authProvider, {
      serverUrl: this._url,
      authorizationCode: e,
      resourceMetadataUrl: this._resourceMetadataUrl,
      scope: this._scope,
      fetchFn: this._fetchWithInit
    }) !== "AUTHORIZED")
      throw new lr("Failed to authorize");
  }
  async close() {
    this._reconnectionTimeout && (clearTimeout(this._reconnectionTimeout), this._reconnectionTimeout = void 0), this._abortController?.abort(), this.onclose?.();
  }
  async send(e, r) {
    try {
      const { resumptionToken: n, onresumptiontoken: s } = r || {};
      if (n) {
        this._startOrAuthSse({ resumptionToken: n, replayMessageId: Bi(e) ? e.id : void 0 }).catch((g) => this.onerror?.(g));
        return;
      }
      const i = await this._commonHeaders();
      i.set("content-type", "application/json"), i.set("accept", "application/json, text/event-stream");
      const o = {
        ...this._requestInit,
        method: "POST",
        headers: i,
        body: JSON.stringify(e),
        signal: this._abortController?.signal
      }, a = await (this._fetch ?? fetch)(this._url, o), c = a.headers.get("mcp-session-id");
      if (c && (this._sessionId = c), !a.ok) {
        const g = await a.text().catch(() => null);
        if (a.status === 401 && this._authProvider) {
          if (this._hasCompletedAuthFlow)
            throw new dr(401, "Server returned 401 after successful authentication");
          const { resourceMetadataUrl: S, scope: k } = ll(a);
          if (this._resourceMetadataUrl = S, this._scope = k, await ps(this._authProvider, {
            serverUrl: this._url,
            resourceMetadataUrl: this._resourceMetadataUrl,
            scope: this._scope,
            fetchFn: this._fetchWithInit
          }) !== "AUTHORIZED")
            throw new lr();
          return this._hasCompletedAuthFlow = !0, this.send(e);
        }
        if (a.status === 403 && this._authProvider) {
          const { resourceMetadataUrl: S, scope: k, error: y } = ll(a);
          if (y === "insufficient_scope") {
            const b = a.headers.get("WWW-Authenticate");
            if (this._lastUpscopingHeader === b)
              throw new dr(403, "Server returned 403 after trying upscoping");
            if (k && (this._scope = k), S && (this._resourceMetadataUrl = S), this._lastUpscopingHeader = b ?? void 0, await ps(this._authProvider, {
              serverUrl: this._url,
              resourceMetadataUrl: this._resourceMetadataUrl,
              scope: this._scope,
              fetchFn: this._fetch
            }) !== "AUTHORIZED")
              throw new lr();
            return this.send(e);
          }
        }
        throw new dr(a.status, `Error POSTing to endpoint: ${g}`);
      }
      if (this._hasCompletedAuthFlow = !1, this._lastUpscopingHeader = void 0, a.status === 202) {
        await a.body?.cancel(), ny(e) && this._startOrAuthSse({ resumptionToken: void 0 }).catch((g) => this.onerror?.(g));
        return;
      }
      const l = (Array.isArray(e) ? e : [e]).filter((g) => "method" in g && "id" in g && g.id !== void 0).length > 0, h = a.headers.get("content-type"), p = dS(h);
      if (l)
        if (p === "text/event-stream")
          this._handleSseStream(a.body, { onresumptiontoken: s }, !1);
        else if (p === "application/json") {
          const g = await a.json(), S = Array.isArray(g) ? g.map((k) => ms.parse(k)) : [ms.parse(g)];
          for (const k of S)
            this.onmessage?.(k);
        } else
          throw await a.body?.cancel(), new dr(-1, `Unexpected content type: ${h}`);
      else
        await a.body?.cancel();
    } catch (n) {
      throw this.onerror?.(n), n;
    }
  }
  get sessionId() {
    return this._sessionId;
  }
  /**
   * Terminates the current session by sending a DELETE request to the server.
   *
   * Clients that no longer need a particular session
   * (e.g., because the user is leaving the client application) SHOULD send an
   * HTTP DELETE to the MCP endpoint with the Mcp-Session-Id header to explicitly
   * terminate the session.
   *
   * The server MAY respond with HTTP 405 Method Not Allowed, indicating that
   * the server does not allow clients to terminate sessions.
   */
  async terminateSession() {
    if (this._sessionId)
      try {
        const e = await this._commonHeaders(), r = {
          ...this._requestInit,
          method: "DELETE",
          headers: e,
          signal: this._abortController?.signal
        }, n = await (this._fetch ?? fetch)(this._url, r);
        if (await n.body?.cancel(), !n.ok && n.status !== 405)
          throw new dr(n.status, `Failed to terminate session: ${n.statusText}`);
        this._sessionId = void 0;
      } catch (e) {
        throw this.onerror?.(e), e;
      }
  }
  setProtocolVersion(e) {
    this._protocolVersion = e;
  }
  get protocolVersion() {
    return this._protocolVersion;
  }
  /**
   * Resume an SSE stream from a previous event ID.
   * Opens a GET SSE connection with Last-Event-ID header to replay missed events.
   *
   * @param lastEventId The event ID to resume from
   * @param options Optional callback to receive new resumption tokens
   */
  async resumeStream(e, r) {
    await this._startOrAuthSse({
      resumptionToken: e,
      onresumptiontoken: r?.onresumptiontoken
    });
  }
}
const YS = [
  "This server exposes the wpDataTables WordPress plugin (tables, charts, media, SQL constructor, plugin settings).",
  "",
  "Tool names mirror the wpDataTables abilities with hyphens, e.g. `wpdatatables-list-tables`,",
  "`wpdatatables-get-table-info`, `wpdatatables-create-simple-table`, `wpdatatables-list-charts`,",
  "`wpdatatables-open-table-editor`. Angie prefixes them with the server name (`wpDataTables__<tool>`);",
  "call whatever names appear in tools/list and never claim the tools are unavailable without calling tools/list first.",
  "",
  "Answer wpDataTables questions with these tools instead of guessing: start from `wpdatatables-list-tables` or",
  "`wpdatatables-list-charts` for discovery, then `wpdatatables-get-table-info` / `wpdatatables-get-chart-info` for details.",
  "For SQL-backed tables use `wpdatatables-list-db-tables` and `wpdatatables-describe-db-table` before",
  "`wpdatatables-create-table-from-query`. Use `wpdatatables-open-table-editor` / `wpdatatables-open-chart-wizard` for navigation.",
  "",
  "FORMATTING: whenever you list or mention a table or chart, render it as a markdown link built from the",
  "`adminLink` field in tool output, e.g. [Table title](adminLink)."
].join(`
`);
function ml(t) {
  return t.endsWith("/") ? t : `${t}/`;
}
function XS() {
  const t = window.wpWdtApiSettings?.root || window.wpApiSettings?.root || "/wp-json/";
  return /^https?:\/\//.test(t) ? ml(t) : ml(new URL(t, window.location.origin).toString());
}
function Sa(t) {
  const e = t instanceof Error ? t.message : String(t);
  return e.includes("-32005") || e.includes("Session not found") || e.includes("404");
}
async function ef(t) {
  const e = new aS({ name: "wpdatatables-angie-proxy", version: "1.0.0" });
  return await e.connect(
    new QS(new URL(t), {
      requestInit: { credentials: "include" }
    })
  ), e;
}
async function tf(t) {
  await new Promise((e) => setTimeout(e, t));
}
async function ek(t, e = 4) {
  for (let r = 1; ; r++)
    try {
      return await ef(t);
    } catch (n) {
      if (r >= e || !Sa(n)) throw n;
      await tf(r * 300 + Math.floor(Math.random() * 250));
    }
}
async function tk(t, e = 4) {
  for (let r = 1; ; r++)
    try {
      const n = await ef(t), s = (await n.listTools()).tools.map(({ outputSchema: i, ...o }) => o);
      return { phpClient: n, tools: s };
    } catch (n) {
      if (r >= e || !Sa(n)) throw n;
      await tf(r * 300 + Math.floor(Math.random() * 250));
    }
}
async function rk() {
  const t = window.wpWdtApiSettings?.nonce || window.wpApiSettings?.nonce, e = new URL("mcp/wpdatatables-mcp-server", XS());
  t && e.searchParams.set("_wpnonce", t);
  let { phpClient: r, tools: n } = await tk(e);
  const s = new rS(
    { name: "wpdatatables-mcp-service", version: "1.0.0" },
    { capabilities: { tools: {} }, instructions: YS }
  );
  return s.server.setRequestHandler(Ts, async () => ({ tools: n })), s.server.setRequestHandler(tn, async (i) => {
    const o = {
      name: i.params.name,
      arguments: i.params.arguments ?? {}
    };
    try {
      return await r.callTool(o);
    } catch (a) {
      if (!Sa(a)) throw a;
      return r = await ek(e), await r.callTool(o);
    }
  }), s;
}
const gl = async () => {
  try {
    const t = await rk();
    await new yv().registerLocalServer({
      name: "wpDataTables",
      title: "wpDataTables",
      version: "1.0.0",
      description: "wpDataTables – Tables and Charts for WordPress. You can create, list, inspect, edit, and open tables and charts with these tools (wpdatatables-create-simple-table, wpdatatables-create-table-from-source, wpdatatables-create-table-from-query, wpdatatables-list-tables, wpdatatables-list-charts). Never say you cannot create a wpDataTable without calling tools/list first. Whenever you list or mention tables or charts, render each as a clickable markdown link using adminLink from tool outputs (e.g. [Table title](adminLink)).",
      capabilities: { tools: {} },
      server: t
    });
  } catch (t) {
    console.error("❌ [wpDataTables MCP] Registration failed:", t);
  }
};
document.readyState === "loading" ? document.addEventListener("DOMContentLoaded", gl) : gl();

<template>
  <el-dialog
    class="enterFormClass"
    title="Event Creation"
    :visible.sync="dialogVisible"
    :before-close="beforeClose"
    width="70%"
  >
    <span>Please enter necessary information before continue.</span>
    <el-form ref="ruleForm" :rules="rules" :model="form" class="form-inline">
      <el-form-item label="Event Date" prop="date">
        <el-date-picker
          v-model="form.date"
          type="daterange"
          align="right"
          unlink-panels
          :picker-options="pickerOptions"
          range-separator="~"
          start-placeholder="Start Date"
          end-placeholder="End Date"
          value-format="yyyy-MM-dd"
        >
        </el-date-picker>
      </el-form-item>
      <el-form-item label="Postcode" prop="postcode">
        <el-input required v-model="form.postcode"></el-input>
      </el-form-item>
    </el-form>
    <span slot="footer" class="dialog-footer">
      <el-button size="small" @click="beforeClose">Cancel</el-button>
      <el-button size="small" type="primary" @click="onSubmit"
        >Confirm</el-button
      >
    </span>
  </el-dialog>
</template>

<script>
export default {
  name: "wooForm",
  data() {
    return {
      dialogVisible: false,
      form: {
        date: [],
        postcode: "",
      },
      pickerOptions: {
        disabledDate(time) {
          return time.getTime() < (Date.now() + 1000 * 60 * 60 * 24);
        },
      },
      rules: {
        date: [
          {
            required: true,
            message: "Please select event date",
            trigger: "blur",
          },
        ],
        postcode: [
          {
            required: true,
            message: "Please input event postcode",
            trigger: "blur",
          },
        ],
      },
      resolve: null,
      reject: () => false,
      eventSetting: null,
    };
  },
  methods: {
    open(eventSetting) {
      this.eventSetting = eventSetting;
      if (eventSetting) {
        this.form.date = eventSetting.date || [];
        this.form.postcode = eventSetting.postcode || "";
      }
      this.dialogVisible = true;
      return new Promise((resolve, reject) => {
        this.resolve = resolve;
        this.reject = reject;
      });
    },
    onSubmit() {
      this.$refs["ruleForm"].validate((valid) => {
        if (valid) {
          if (this.resolve) {
            this.resolve(this.form);
          } else {
            this.$emit("update", this.form);
          }
          this.dialogVisible = false;
        } else {
          return false;
        }
      });
    },
    beforeClose() {
      if (this.form.date.length == 0 || this.form.postcode == "") {
        this.$alert(
          "Please enter necessary information before continue.",
          "Alert",
          {
            confirmButtonText: "Yes",
            callback: () => {
              console.log("done");
            },
          }
        );
      } else {
        this.dialogVisible = false;
      }
    },
  },
};
</script>

<style lang="scss">
.enterFormClass .el-dialog__body {
  padding: 10px 20px;
}
.form-inline {
  .el-input__inner {
    height: 40px;
  }
  .el-input {
    width: auto;
  }
}
</style>
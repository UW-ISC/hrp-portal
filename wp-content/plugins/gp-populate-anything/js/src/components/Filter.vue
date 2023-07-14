<template>
	<div class="gppa-filter" :aria-label="i18nStrings.filterAriaLabel.gformFormat( index + 1 )" aria-role="group">
		<select :disabled="loadingProperties"
				class="gppa-filter-property"
				v-model="filterPropertyModel"
				@change="resetFilter"
				ref="propertySelect">
			<option v-if="loadingProperties" value="" selected disabled hidden>{{ i18nStrings.loadingEllipsis }}</option>

			<option v-for="option in filterPropertiesUngrouped" v-bind:value="option.value">
				{{ truncateStringMiddle(option.label) }}
			</option>

			<optgroup v-for="(options, groupID) in filterPropertiesGrouped"
					  v-bind:label="groupID in objectTypeInstance.groups && objectTypeInstance.groups[groupID].label">
				<option v-for="option in options" v-bind:value="option.value">
					{{ truncateStringMiddle(option.label) }}
				</option>
			</optgroup>
		</select>

		<select class="gppa-filter-operator" v-model="filter.operator"
				:disabled="!Object.keys(properties).length || (Object.keys(properties).length === 1 && 'primary-property' in properties) || !(filter.property in propertyValues)">
			<option v-for="operator in operators" v-bind:value="operator">{{ i18nStrings.operators[operator]
				}}
			</option>
		</select>

		<gppa-select-with-custom
			 :loading="loadingPropertyValues"
			 additional-class="gppa-filter-value"
			 v-model="filter.value"
			 :operator="filter.operator"
			 :object-type-instance="objectTypeInstance"
			 :flattened-properties="flattenedProperties">
			<option v-if="!filter.value" value="" disabled selected="selected" hidden>&ndash; Value &ndash;</option>

			<optgroup :label="i18nStrings.specialValues">
				<option value="gf_custom">{{ i18nStrings.addCustomValue }}</option>

				<option v-for="(option, optionIndex) in specialValues"
						v-bind:value="option.value"
						:selected="option.value == filter.value">
					{{ option.label }}
				</option>
			</optgroup>

			<optgroup :label="i18nStrings.formFieldValues" v-if="formFieldValues && formFieldValues.length">
				<option v-for="(option, optionIndex) in formFieldValues"
						v-bind:value="option.value"
						:selected="option.value == filter.value">
					{{ truncateStringMiddle(option.label) }}
				</option>
			</optgroup>

			<option v-for="(option, optionIndex) in propertyValues[filter.property]"
					v-bind:value="option.value"
					v-bind:disabled="option.disabled"
					:selected="option.value == filter.value">
				{{ truncateStringMiddle(option.label) }}
			</option>
		</gppa-select-with-custom>

		<div class="repeater-buttons">
			<button class="add-item gform-st-icon gform-st-icon--circle-plus" @click="$emit('add-filter')" :title="i18nStrings.addFilter" />
			<button class="remove-item gform-st-icon gform-st-icon--circle-minus" @click="$emit('remove-filter')" :title="i18nStrings.removeFilter" :aria-label="i18nStrings.removeFilterAriaLabel.gformFormat( index + 1 )" />
		</div>

		<div
			v-if="filters.length > 1 && index !== filters.length - 1"
			class="gppa-filter-and" :aria-label="i18nStrings.and">
			{{ i18nStrings.and }}
		</div>
	</div>
</template>

<script lang="ts">
	import Vue from 'vue';
	import truncateStringMiddle from '../helpers/truncateStringMiddle';
	import SelectWithCustom from './SelectWithCustom.vue';

	export default Vue.extend({
		props: [
			'filter',
			'filters',
			'index',
			'field',
			'properties',
			'flattenedProperties',
			'propertyValues',
			'ungroupedProperties',
			'groupedProperties',
			'objectTypeInstance',
			'getPropertyValues',
		],
		components: {
			'gppa-select-with-custom': SelectWithCustom,
		},
		mounted() {
			// @ts-ignore
			this.$refs.propertySelect?.focus();
		},
		created: function () {
			if (this.filter.property) {
				return this.getPropertyValues(this.filter.property);
			}

			this.filter.property = (Object.values(this.flattenedProperties) as any)[0].value;
		},
		data: function () {
			return {
				i18nStrings: window.GPPA_ADMIN.strings,
				defaultOperator: 'is',
			}
		},
		watch: {
			'filter.property': function (val, oldVal) {
				this.getPropertyValues(val);
			}
		},
		methods: {
			truncateStringMiddle: truncateStringMiddle,
			/**
			 * resetFilter's contents were originally extracted from the 'filter.property' watcher to prevent the
			 * filter value from needlessly resetting to having no value when the field itself changes.
			 */
			resetFilter: function() {
				this.filter.value = '';
				this.filter.operator = this.defaultOperator;
			}
		},
		computed: {
			/* Used so we can set it to an empty value while loading. */
			filterPropertyModel: {
				get() {
					if (this.loadingProperties) {
						return '';
					}

					return this.filter.property;
				},
				set(val) {
					this.filter.property = val;
				}
			},
			loadingProperties: function() {
				return !Object.keys(this.properties).length || (Object.keys(this.properties).length === 1 && 'primary-property' in this.properties);
			},
			loadingPropertyValues: function() {
				return !(this.filter.property in this.propertyValues);
			},
			specialValues: function () {
				const specialValues = [
					{
						label: 'Current User ID',
						value: 'special_value:current_user:ID',
					},
					{
						label: 'Current Post ID',
						value: 'special_value:current_post:ID',
					}
				];

				if (this.objectTypeInstance?.supportsNullFilterValue) {
					specialValues.push({
						label: 'NULL',
						value: 'special_value:null',
					});
				}

				return window.gform.applyFilters( 'gppa_filter_special_values', specialValues, this );
			},
			formFieldValues: function () {

				var formFieldValues = [];

				const excludedFormFieldValueInputTypes = ['chainedselect'];

				for (var i = 0; i < window.form.fields.length; i++) {

					const field = window.form.fields[i];
					const inputType = window.GetInputType(field);

					if (excludedFormFieldValueInputTypes.includes(inputType)) {
						continue;
					}

					if (window.IsConditionalLogicField(field) || ['date'].includes(inputType)) {

						if (field.inputs && !['checkbox', 'email'].includes(inputType)) {
							for (var j = 0; j < field.inputs.length; j++) {
								var input = field.inputs[j];
								if (!input.isHidden) {
									formFieldValues.push({
										label: window.GetLabel(field, input.id),
										value: 'gf_field:' + input.id,
									});
								}
							}
						} else {
							formFieldValues.push({
								label: window.GetLabel(field),
								value: 'gf_field:' + field.id,
							});
						}

					}

				}

				return formFieldValues;

			},
			operators: function () {

				/* Labels for operators are pulled from i18nStrings in the Vue bindings */
				if (this.filter.property in this.flattenedProperties) {
					var property = this.flattenedProperties[this.filter.property];

					if ('operators' in property) {
						return property.operators;
					}

					if ('group' in property) {
						var group = this.objectTypeInstance.groups[property.group];

						if ('operators' in group) {
							return group.operators;
						}
					}
				}

				return window.GPPA_ADMIN.defaultOperators;

			},
			filterPropertiesGrouped: function () {
				const groupedProperties: { [groupId: string]: any[] } = {...this.groupedProperties};

				for ( const [groupId, properties] of Object.entries(groupedProperties) ) {
					groupedProperties[groupId] = properties.filter(property => property?.['supports_filters'] !== false);

					if (groupedProperties[groupId].length === 0) {
						delete groupedProperties[groupId];
					}
				}

				return groupedProperties;
			},
			filterPropertiesUngrouped: function () {
				return this.ungroupedProperties?.filter((property: any) => property?.['supports_filters'] !== false);
			},
		},
	});
</script>
